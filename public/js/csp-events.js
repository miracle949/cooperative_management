/* csp-events.js
 * Event-delegation dispatcher that replaces inline event-handler attributes
 * (onclick / onchange / oninput / onsubmit / onkeyup / onkeydown / ondblclick),
 * which the strict nonce Content-Security-Policy forbids. Existing global
 * functions are called unchanged; only the binding mechanism changes.
 *
 * Usage:
 *   <button data-action="myFunc" data-arg='["arg1", "|el|", "|event|"]'>...
 *
 *   data-arg is an optional JSON array of positional arguments. These tokens
 *   are resolved at dispatch time: |event| -> event, |el| -> element,
 *   |value| -> element.value, |src| -> element.src, |dsrc| -> element.dataset.src,
 *   |checked| -> element.checked.
 *
 * Extra attributes:
 *   data-prevent           -> e.preventDefault() before invoking the handler
 *   data-stop              -> e.stopPropagation() before invoking the handler
 *   data-confirm="Msg"     -> confirm() the click; cancels when rejected
 *   data-submit-on-change  -> (change events) submit closest form
 *   data-form-id="id"      -> for data-submit-on-change, submit this form instead
 *   data-numeric-guard     -> (keydown) block e/E/+/- in number inputs
 *   data-overlay           -> (click) hide element when the backdrop itself is hit
 *   data-fallback="url"    -> (img error) swap in a fallback src
 *
 * Page-specific glue actions can be registered:
 *   CSP_actions.register('name', function (event, element) { ... });
 */
(function (global) {
    'use strict';

    if (global.__cspEventsLoaded) return;
    global.__cspEventsLoaded = true;

    var actions = {};

    function register(name, fn) {
        actions[name] = fn;
    }

    function closestAttr(el, attr) {
        if (el && typeof el.closest === 'function') {
            var found = el.closest('[' + attr + ']');
            if (found) return found;
        }
        return null;
    }

    function readArgs(el) {
        if (!el || !el.dataset || !el.dataset.arg) return [];
        try {
            return JSON.parse(el.dataset.arg);
        } catch (e) {
            return [];
        }
    }

    function resolveTokens(list, e, el) {
        for (var i = 0; i < list.length; i++) {
            var v = list[i];
            if (v === '|event|') {
                list[i] = e;
            } else if (v === '|el|') {
                list[i] = el;
            } else if (v === '|value|') {
                list[i] = el.value;
            } else if (v === '|src|') {
                list[i] = el.getAttribute ? el.getAttribute('src') : el.src;
            } else if (v === '|dsrc|') {
                list[i] = el.dataset ? el.dataset.src : undefined;
            } else if (v === '|checked|') {
                list[i] = el.checked;
            }
        }
        return list;
    }

    function runHandler(e, el) {
        var name = el.getAttribute('data-action');
        if (!name) return undefined;
        var args = resolveTokens(readArgs(el), e, el);
        var handler = actions[name];
        if (typeof handler === 'function') {
            if (el.getAttribute('data-arg') === null) args = [e, el];
            return handler.apply(el, args);
        }
        if (typeof global[name] === 'function') return global[name].apply(el, args);
        return undefined;
    }

    function dispatch(e) {
        var el = closestAttr(e.target, 'data-action');
        if (!el) return;

        var only = el.getAttribute('data-trigger');
        if (only) {
            var types = only.split(',');
            if (types.indexOf(e.type) === -1) return;
        }

        if (el.hasAttribute('data-stop') && e.stopPropagation) {
            e.stopPropagation();
        }

        if (el.hasAttribute('data-confirm') && typeof global.confirm === 'function') {
            if (!global.confirm(el.getAttribute('data-confirm'))) {
                if (e.preventDefault) e.preventDefault();
                return;
            }
        }

        if (el.hasAttribute('data-prevent') && e.preventDefault) {
            e.preventDefault();
        }

        var result = runHandler(e, el);
        if (result === false && e.preventDefault) e.preventDefault();
    }

    function dispatchSubmit(e) {
        var el = closestAttr(e.target, 'data-action');
        if (!el) return;

        if (el.hasAttribute('data-prevent-submit')) {
            e.preventDefault();
            return;
        }

        var result = runHandler(e, el);
        if (result === false) e.preventDefault();
    }

    function dispatchChange(e) {
        var t = e.target;
        if (t && typeof t.closest === 'function') {
            var se = t.closest('[data-submit-on-change]');
            if (se) {
                var formId = se.getAttribute('data-form-id');
                var form = null;
                if (formId) {
                    form = document.getElementById(formId);
                } else if (se.form) {
                    form = se.form;
                } else {
                    form = t.closest('form');
                }
                if (form && typeof form.submit === 'function') {
                    form.submit();
                }
            }
        }
        dispatch(e);
    }

    function dispatchKeydown(e) {
        var t = e.target;
        if (t && typeof t.closest === 'function' && t.closest('[data-numeric-guard]')) {
            var k = e.key;
            if (k === 'e' || k === 'E' || k === '+' || k === '-') {
                e.preventDefault();
            }
        }
        dispatch(e);
    }

    function dispatchOverlay(e) {
        var t = e.target;
        if (!t || typeof t.closest !== 'function') return;
        var el = t.closest('[data-overlay]');
        if (!el) return;
        if (t === el) el.style.display = 'none';
    }

    function dispatchError(e) {
        var t = e.target;
        if (!t || typeof t.closest !== 'function') return;
        var el = t.closest('[data-error-mark], [data-fallback]');
        if (!el) return;
        var mark = el.getAttribute('data-error-mark');
        if (mark) {
            global[mark] = true;
            return;
        }
        if (el.tagName && el.tagName.toLowerCase() === 'img') {
            var fb = el.getAttribute('data-fallback');
            if (!fb) return;
            if (el.getAttribute('src') === fb) {
                el.style.display = 'none';
                return;
            }
            el.setAttribute('src', fb);
            el.removeAttribute('data-fallback');
        }
    }

    function onClick(e) {
        dispatchOverlay(e);
        dispatch(e);
    }

    document.addEventListener('click', onClick);
    document.addEventListener('change', dispatchChange);
    document.addEventListener('input', dispatch);
    document.addEventListener('keyup', dispatch);
    document.addEventListener('keydown', dispatchKeydown);
    document.addEventListener('submit', dispatchSubmit);
    document.addEventListener('dblclick', dispatch);
    document.addEventListener('error', dispatchError, true);

    register('navigate', function (e, el) {
        window.location = el.getAttribute('data-url');
    });

    register('show-modal', function (e, el) {
        var target = document.getElementById(el.getAttribute('data-target'));
        if (target) target.style.display = 'flex';
    });

    register('hide-modal', function (e, el) {
        var target = document.getElementById(el.getAttribute('data-target'));
        if (target) target.style.display = 'none';
    });

    register('remove-element', function (e, el) {
        var target = document.getElementById(el.getAttribute('data-target'));
        if (target && typeof target.remove === 'function') target.remove();
    });

    register('remove-closest', function (e, el) {
        var sel = el.getAttribute('data-sel');
        if (!sel) return;
        var target = el.closest(sel);
        if (target && typeof target.remove === 'function') target.remove();
    });

    register('set-input-value', function (e, el) {
        var target = document.getElementById(el.getAttribute('data-target'));
        if (target) target.value = el.getAttribute('data-value');
    });

    register('remove-parent', function (e, el) {
        if (el.parentElement) el.parentElement.remove();
    });

    register('goto-year', function (e, el) {
        window.location = (el.getAttribute('data-base-url') || window.location.pathname) + '?year=' + el.value;
    });

    register('stop-propagation', function (e) {
        if (e.stopPropagation) e.stopPropagation();
    });

    global.CSP_actions = {
        register: register
    };
})(window);
<div id="switchModal"
    style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.55); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; max-width:420px; width:90%; padding:1.5rem; box-shadow:0 10px 40px rgba(0,0,0,.3);">
        <div class="switch-top" style="display: flex; align-items: center; gap: 0.5rem; margin: 0 0 0.1rem;">
            <i class="fa fa-shield" data-lucide="shield-alert" style="display:inline-block; width:15px; height:15px; vertical-align:-3px;"></i> 
            <h3 style="margin: 0; font-size:1.15rem;">
            Someone is trying to log in
        </h3>
        </div>
        <p style="font-size:.9rem; color:#444; margin-bottom:.6rem;">
            Your account is being opened on another device or browser.
        </p>
        <p id="switchInfo" style="font-size:.8rem; color:#666; background:#f5f5f5; padding:.6rem; border-radius:6px; word-break:break-word;"></p>
        <p style="font-size:.85rem; color:#444; margin:.8rem 0 1rem;">
            If it's you, tap <b>Switch</b>. You'll be logged out here and signed in there.
            If it's not you, tap <b>Deny</b> and change your password.
        </p>
        <div style="display:flex; gap:.6rem; justify-content:flex-end;">
            <button type="button" id="switchDeny"
                style="padding:.55rem 1rem; border:1px solid #ccc; background:#fff; border-radius:6px; cursor:pointer; font-size: 14.5px; font-weight: 600;">Deny</button>
            <button type="button" id="switchAllow"
                style="padding:.55rem 1rem; border:0; background:#1f2a52; color:#fff; border-radius:6px; cursor:pointer; font-size: 14.5px; font-weight: 600;">Switch to that device</button>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    (function () {
        var PENDING_URL = "{{ route('login.switch.pending') }}";
        var RESPOND_URL = "{{ route('login.switch.respond') }}";
        var CSRF = "{{ csrf_token() }}";

        var modal = document.getElementById('switchModal');
        var info  = document.getElementById('switchInfo');
        var currentId = null;
        var busy = false;

        function respond(decision) {
            if (busy || !currentId) return;
            busy = true;
            fetch(RESPOND_URL, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentId, decision: decision })
            }).then(function (r) { return r.json(); }).then(function (d) {
                if (d.redirect) { window.location.href = d.redirect; return; }
                modal.style.display = 'none';
                currentId = null;
            }).catch(function () {
                modal.style.display = 'none';
            }).finally(function () { busy = false; });
        }

        function poll() {
            fetch(PENDING_URL, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) {
                    if (r.status === 401 || r.status === 419) { return { pending: false }; }
                    return r.json();
                })
                .then(function (d) {
                    if (d.pending) {
                        currentId = d.id;
                        info.textContent = d.when + ' · IP ' + d.ip + ' · ' + (d.device || 'Unknown device');
                        modal.style.display = 'flex';
                    } else if (!busy) {
                        modal.style.display = 'none';
                        currentId = null;
                    }
                })
                .catch(function () {});
        }

        document.getElementById('switchAllow').addEventListener('click', function () { respond('approve'); });
        document.getElementById('switchDeny').addEventListener('click',  function () { respond('deny'); });

        poll();
        setInterval(poll, 4000);
    })();
</script>
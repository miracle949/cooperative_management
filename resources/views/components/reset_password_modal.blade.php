<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-content">
            <div class="modal-header rp-header">
                <div class="rp-header-left">
                    <div class="rp-icon"><i class="fa fa-lock"></i></div>
                    <div>
                        <h5 class="rp-title">Reset Password</h5>
                        <p class="rp-subtitle">Enter your current password and choose a new one</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body rp-body">
                <form id="resetPasswordForm">
                    @csrf
                    <div class="rp-field">
                        <label class="rp-label">Current Password</label>
                        <div class="rp-input-wrap">
                            <input type="password" class="rp-input" id="current_password" name="current_password"
                                required>
                            <button type="button" class="rp-toggle" data-toggle-target="current_password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <small class="rp-error" id="current_password_error"></small>
                    </div>

                    <div class="rp-field">
                        <label class="rp-label">New Password</label>
                        <div class="rp-input-wrap">
                            <input type="password" class="rp-input" id="new_password" name="new_password" minlength="8"
                                required>
                            <button type="button" class="rp-toggle" data-toggle-target="new_password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <small class="rp-hint">Minimum 8 characters</small>
                    </div>

                    <div class="rp-field">
                        <label class="rp-label">Confirm New Password</label>
                        <div class="rp-input-wrap">
                            <input type="password" class="rp-input" id="new_password_confirmation"
                                name="new_password_confirmation" minlength="8" required>
                            <button type="button" class="rp-toggle" data-toggle-target="new_password_confirmation">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <small class="rp-error" id="confirm_password_error"></small>
                    </div>

                    <button type="submit" class="rp-btn-confirm" id="resetPasswordSubmitBtn">
                        <i class="fa fa-check"></i> Update Password
                    </button>
                    <button type="button" class="rp-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    #resetPasswordModal .rp-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .15);
    }

    #resetPasswordModal .rp-header {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #resetPasswordModal .rp-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #resetPasswordModal .rp-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: var(--teal, #1E2A4A);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
    }

    #resetPasswordModal .rp-title {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
    }

    #resetPasswordModal .rp-subtitle {
        margin: 3px 0 0;
        font-size: 13px;
        color: var(--muted, #6b7a99);
    }

    #resetPasswordModal .rp-body {
        padding: 1.5rem;
        background: #fff;
    }

    #resetPasswordModal .rp-field {
        margin-bottom: 1.1rem;
    }

    #resetPasswordModal .rp-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    #resetPasswordModal .rp-input-wrap {
        position: relative;
    }

    #resetPasswordModal .rp-input {
        width: 100%;
        height: 46px;
        padding: 0 42px 0 14px;
        border: 1.5px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        color: #333;
        box-sizing: border-box;
        outline: none;
    }

    #resetPasswordModal .rp-input:focus {
        border-color: var(--teal, #1E2A4A);
    }

    #resetPasswordModal .rp-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        font-size: 14px;
    }

    #resetPasswordModal .rp-toggle:hover {
        color: #374151;
    }

    #resetPasswordModal .rp-error {
        display: block;
        min-height: 14px;
        margin-top: 4px;
        font-size: 12px;
        color: #dc2626;
    }

    #resetPasswordModal .rp-hint {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
    }

    #resetPasswordModal .rp-btn-confirm {
        width: 100%;
        padding: 12px;
        margin-top: 4px;
        background: var(--teal, #1E2A4A);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    #resetPasswordModal .rp-btn-confirm:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    #resetPasswordModal .rp-btn-cancel {
        width: 100%;
        padding: 11px;
        margin-top: 8px;
        background: #fff;
        color: var(--muted, #6b7a99);
        border: 1.5px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        cursor: pointer;
    }

    .rp-toast {
        position: fixed;
        right: 20px;
        top: 20px;
        z-index: 99999;
        width: 280px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        color: #16a34a;
        border: 1px solid #E2E8E5;
        border-left: 5px solid #16a34a;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        font-size: 13.5px;
        font-weight: 600;
    }

    .rp-toast span {
        color: #111827;
    }
</style>

<script nonce="{{ csp_nonce() }}">
    (function () {
        var modal = document.getElementById('resetPasswordModal');
        if (!modal) return;

        // Move to <body> so page wrappers can't break the modal's positioning
        document.body.appendChild(modal);

        var form = document.getElementById('resetPasswordForm');
        var currentErr = document.getElementById('current_password_error');
        var confirmErr = document.getElementById('confirm_password_error');
        var submitBtn = document.getElementById('resetPasswordSubmitBtn');

        modal.querySelectorAll('.rp-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-target'));
                var icon = btn.querySelector('i');
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            });
        });

        modal.addEventListener('hidden.bs.modal', function () {
            form.reset();
            currentErr.textContent = '';
            confirmErr.textContent = '';
            modal.querySelectorAll('.rp-input').forEach(function (i) { i.type = 'password'; });
            modal.querySelectorAll('.rp-toggle i').forEach(function (i) {
                i.classList.add('fa-eye');
                i.classList.remove('fa-eye-slash');
            });
        });

        function toast(message) {
            var old = document.getElementById('rpToast');
            if (old) old.remove();
            var el = document.createElement('div');
            el.id = 'rpToast';
            el.className = 'rp-toast';
            el.innerHTML = '<i class="fa fa-check-circle"></i><span></span>';
            el.querySelector('span').textContent = message;
            document.body.appendChild(el);
            setTimeout(function () { el.remove(); }, 3000);
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            currentErr.textContent = '';
            confirmErr.textContent = '';

            var newPassword = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('new_password_confirmation').value;

            if (newPassword !== confirmPassword) {
                confirmErr.textContent = 'Passwords do not match.';
                return;
            }

            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';

            try {
                var response = await fetch('{{ route("ChangePassword") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        current_password: document.getElementById('current_password').value,
                        new_password: newPassword,
                        new_password_confirmation: confirmPassword,
                    }),
                });

                var data = await response.json().catch(function () { return {}; });

                if (response.ok && data.success) {
                    modal.querySelector('[data-bs-dismiss="modal"]').click();
                    toast(data.message || 'Password updated successfully.');
                } else {
                    var msg = data.errors ? Object.values(data.errors)[0][0] : data.message;
                    currentErr.textContent = msg || 'Something went wrong.';
                }
            } catch (err) {
                currentErr.textContent = 'Something went wrong. Please try again.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    })();
</script>
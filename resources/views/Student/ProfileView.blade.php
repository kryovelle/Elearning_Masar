@extends('layouts.student')

@section('content')
<div class="profile-page">

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="student_loading_profile">Loading your profile...</p>
    </div>

    <div id="profileContent" style="display:none;">

        <div class="profile-card">
            <h2 data-i18n="student_profile_info">Profile Information</h2>
            <div id="profileError" class="auth-error" style="display:none;"></div>
            <div id="profileSuccess" class="auth-success" style="display:none;"></div>

            <form id="profileForm" class="auth-form">
                <div class="photo-upload-row">
                    <div class="photo-preview" id="photoPreview"><i class="fas fa-user"></i></div>
                    <div>
                        <label for="photoInput" class="btn-upload-photo" data-i18n="student_upload_photo">Upload Photo</label>
                        <input type="file" id="photoInput" accept="image/*" style="display:none;">
                    </div>
                </div>

                <label for="nameInput" data-i18n="register_name_label">Full name</label>
                <input type="text" id="nameInput" required>

                <label for="emailInput" data-i18n="register_email_label">Email</label>
                <input type="email" id="emailInput" required>

                <label for="phoneInput" data-i18n="register_phone_label">Phone</label>
                <input type="tel" id="phoneInput" required>

                <button type="submit" class="btn-auth" data-i18n="student_save_changes">Save Changes</button>
            </form>

            <button type="button" id="openChangePasswordBtn" class="btn-secondary-link" data-i18n="student_change_password">Change Password</button>
        </div>

        <div class="profile-card">
            <h2 data-i18n="student_2fa_title">Two-Factor Authentication</h2>

            <div id="mfaDisabledView">
                <p class="mfa-status"><i class="fas fa-times-circle status-off"></i> <span data-i18n="student_2fa_disabled">Disabled</span></p>
                <button type="button" id="openEnableMfaBtn" class="btn-auth" data-i18n="student_enable_2fa">Enable 2FA</button>
            </div>

            <div id="mfaEnabledView" style="display:none;">
                <p class="mfa-status"><i class="fas fa-check-circle status-on"></i> <span data-i18n="student_2fa_enabled">Enabled</span></p>
                <div class="mfa-actions">
                    <button type="button" id="openRegenerateBtn" class="btn-secondary" data-i18n="student_regenerate_codes">Regenerate Recovery Codes</button>
                    <button type="button" id="openDisableMfaBtn" class="btn-danger" data-i18n="student_disable_2fa">Disable 2FA</button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Change password popup -->
<div id="changePasswordPopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_change_password">Change Password</h3>
            <button class="popup-close" onclick="closePopup('changePasswordPopup')">&times;</button>
        </div>
        <div id="pwdError" class="auth-error" style="display:none;"></div>
        <form id="changePasswordForm" class="auth-form">
            <label for="currPassword" data-i18n="student_current_password">Current password</label>
            <input type="password" id="currPassword" required>

            <label for="newPassword" data-i18n="student_new_password">New password</label>
            <input type="password" id="newPassword" required>

            <div class="password-strength" id="strengthMeter" style="display:none;">
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <span id="strengthLabel"></span>
            </div>
            <ul class="password-rules" id="passwordRules">
                <li id="ruleLength" data-i18n="register_rule_length">At least 8 characters</li>
                <li id="ruleUpper" data-i18n="register_rule_upper">One uppercase letter</li>
                <li id="ruleLower" data-i18n="register_rule_lower">One lowercase letter</li>
                <li id="ruleNumber" data-i18n="register_rule_number">One number</li>
                <li id="ruleSymbol" data-i18n="register_rule_symbol">One symbol</li>
            </ul>

            <label for="newPasswordConfirm" data-i18n="register_password_confirm_label">Confirm password</label>
            <input type="password" id="newPasswordConfirm" required>

            <div class="popup-actions">
                <button type="button" class="btn-cancel" onclick="closePopup('changePasswordPopup')" data-i18n="student_cancel">Cancel</button>
                <button type="submit" class="btn-auth" data-i18n="student_update_password">Update Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Enable 2FA — Step 1: scan + verify -->
<div id="enableMfaPopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_setup_authenticator">Set Up Authenticator</h3>
            <button class="popup-close" onclick="closePopup('enableMfaPopup')">&times;</button>
        </div>
        <div id="mfaSetupError" class="auth-error" style="display:none;"></div>

        <p class="mfa-step-text" data-i18n="student_scan_qr">1. Scan this QR with your authenticator app</p>
        <div class="qr-wrap" id="qrWrap"><div class="spinner"></div></div>

        <p class="mfa-step-text" data-i18n="student_manual_entry">Or enter manually:</p>
        <div class="manual-code" id="manualSecret"></div>

        <p class="mfa-step-text" data-i18n="student_enter_code">2. Enter the 6-digit code:</p>
        <form id="confirmMfaForm" class="auth-form">
            <input type="text" id="mfaCode" maxlength="6" placeholder="000000" class="code-input">
            <div class="popup-actions">
                <button type="button" class="btn-cancel" onclick="closePopup('enableMfaPopup')" data-i18n="student_cancel">Cancel</button>
                <button type="submit" class="btn-auth" data-i18n="student_verify_enable">Verify &amp; Enable</button>
            </div>
        </form>
    </div>
</div>

<!-- Enable 2FA — Step 2: recovery codes -->
<div id="recoveryCodesPopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_save_recovery_codes">Save Your Recovery Codes</h3>
            <button class="popup-close" onclick="closePopup('recoveryCodesPopup')">&times;</button>
        </div>
        <p class="mfa-confirm-text"><i class="fas fa-check-circle status-on"></i> <span data-i18n="student_2fa_now_enabled">2FA is now enabled</span></p>
        <p class="mfa-step-text" data-i18n="student_store_codes_note">Store these somewhere safe — each works once if you lose your device.</p>

        <div class="recovery-codes-grid" id="recoveryCodesGrid"></div>

        <div class="popup-actions" style="justify-content:flex-start; gap:0.6rem;">
            <button type="button" id="downloadCodesBtn" class="btn-secondary" data-i18n="student_download">Download</button>
            <button type="button" id="copyCodesBtn" class="btn-secondary" data-i18n="student_copy_all">Copy All</button>
        </div>
        <button type="button" id="doneRecoveryBtn" class="btn-auth" style="width:100%; margin-top:1rem;" data-i18n="student_done">Done</button>
    </div>
</div>

<!-- Regenerate recovery codes -->
<div id="regeneratePopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_regenerate_codes">Regenerate Recovery Codes</h3>
            <button class="popup-close" onclick="closePopup('regeneratePopup')">&times;</button>
        </div>
        <p class="warning-text"><i class="fas fa-triangle-exclamation"></i> <span data-i18n="student_old_codes_warning">Old codes stop working immediately.</span></p>
        <div id="regenerateError" class="auth-error" style="display:none;"></div>
        <form id="regenerateForm" class="auth-form">
            <label for="regeneratePassword" data-i18n="student_confirm_password">Confirm with your password:</label>
            <input type="password" id="regeneratePassword" required>
            <div class="popup-actions">
                <button type="button" class="btn-cancel" onclick="closePopup('regeneratePopup')" data-i18n="student_cancel">Cancel</button>
                <button type="submit" class="btn-auth" data-i18n="student_regenerate">Regenerate</button>
            </div>
        </form>
    </div>
</div>

<!-- Disable 2FA -->
<div id="disableMfaPopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_disable_2fa_title">Disable 2FA?</h3>
            <button class="popup-close" onclick="closePopup('disableMfaPopup')">&times;</button>
        </div>
        <p class="warning-text"><i class="fas fa-triangle-exclamation"></i> <span data-i18n="student_disable_2fa_warning">This makes your account less secure.</span></p>
        <div id="disableMfaError" class="auth-error" style="display:none;"></div>
        <form id="disableMfaForm" class="auth-form">
            <label for="disablePassword" data-i18n="student_confirm_password_label">Confirm with your password:</label>
            <input type="password" id="disablePassword" required>
            <div class="popup-actions">
                <button type="button" class="btn-cancel" onclick="closePopup('disableMfaPopup')" data-i18n="student_cancel">Cancel</button>
                <button type="submit" class="btn-danger" data-i18n="student_disable">Disable</button>
            </div>
        </form>
    </div>
</div>
@endsection


@push('scripts')
<script>
let currentUser = null;
let latestRecoveryCodes = [];
/* =========================================================
   Helpers
========================================================= */

function closePopup(id) {
    document.getElementById(id).style.display = 'none';
}


function getApiError(error, fallback = 'Something went wrong. Please try again.') {
    return (
        error?.response?.data?.error ||
        error?.response?.data?.message ||
        fallback
    );
}


/* =========================================================
   Load Settings
========================================================= */

async function fetchSettings() {

    try {

        const res = await studentFetch(
            '/api/Student/getSettingsData'
        );

        if (!res) return;

        const data = res.data;

        currentUser = data.user;

        document.getElementById('loadingState').style.display = 'none';

        document.getElementById('profileContent').style.display = 'block';


        document.getElementById('nameInput').value =
            currentUser.name || '';

        document.getElementById('emailInput').value =
            currentUser.email || '';

        document.getElementById('phoneInput').value =
            currentUser.phone || '';


        if (currentUser.photo_url) {

            document.getElementById('photoPreview').innerHTML =
                `<img src="${currentUser.photo_url}" alt="Profile photo">`;
        }


        toggleMfaView(
            !!currentUser.mfa_enabled
        );

    } catch (error) {

        console.error(
            'Error fetching settings:',
            error
        );

        const loadingState =
            document.getElementById('loadingState');

        loadingState.innerHTML =
            `<p>${getApiError(error, 'Error loading settings.')}</p>`;

        loadingState.style.display = 'block';
    }
}


/* =========================================================
   MFA View
========================================================= */

function toggleMfaView(enabled) {

    document.getElementById(
        'mfaDisabledView'
    ).style.display =
        enabled ? 'none' : 'block';


    document.getElementById(
        'mfaEnabledView'
    ).style.display =
        enabled ? 'block' : 'none';
}


/* =========================================================
   Photo Preview
========================================================= */

document
    .getElementById('photoInput')
    .addEventListener('change', function () {

        const file = this.files[0];

        if (!file) return;


        const reader = new FileReader();


        reader.onload = function (e) {

            document.getElementById(
                'photoPreview'
            ).innerHTML =
                `<img src="${e.target.result}" alt="Profile photo">`;
        };


        reader.readAsDataURL(file);
    });


/* =========================================================
   Save Profile
========================================================= */

document
    .getElementById('profileForm')
    .addEventListener('submit', async function (event) {

        event.preventDefault();


        const errorBox =
            document.getElementById('profileError');

        const successBox =
            document.getElementById('profileSuccess');


        errorBox.style.display = 'none';
        successBox.style.display = 'none';


        const formData = new FormData();


        formData.append(
            'name',
            document.getElementById('nameInput').value
        );


        formData.append(
            'email',
            document.getElementById('emailInput').value
        );


        formData.append(
            'phone',
            document.getElementById('phoneInput').value
        );


        const photoFile =
            document.getElementById('photoInput').files[0];


        if (photoFile) {

            formData.append(
                'photo',
                photoFile
            );
        }


        try {

            const res = await studentFetch(
                '/api/Student/postSettingsData',
                {
                    method: 'POST',
                    data: formData
                }
            );


            if (!res) return;


            const data = res.data;


            if (data.error) {

                errorBox.textContent =
                    data.error;

                errorBox.style.display =
                    'block';

                return;
            }


            successBox.textContent =
                data.success ||
                'Settings saved successfully!';

            successBox.style.display =
                'block';


            // Refresh displayed data
            await fetchSettings();

        } catch (error) {

            console.error(
                'Error saving profile:',
                error
            );


            errorBox.textContent =
                getApiError(
                    error,
                    'Something went wrong. Please try again.'
                );

            errorBox.style.display =
                'block';
        }
    });


/* =========================================================
   Change Password - Open Popup
========================================================= */

document
    .getElementById('openChangePasswordBtn')
    .addEventListener('click', () => {

        document
            .getElementById('changePasswordForm')
            .reset();


        document
            .getElementById('pwdError')
            .style.display = 'none';


        document
            .getElementById('strengthMeter')
            .style.display = 'none';


        document
            .getElementById('changePasswordPopup')
            .style.display = 'flex';
    });


/* =========================================================
   Password Strength
========================================================= */

function evaluatePassword(password) {

    const checks = {

        length:
            password.length >= 8,

        upper:
            /[A-Z]/.test(password),

        lower:
            /[a-z]/.test(password),

        number:
            /[0-9]/.test(password),

        symbol:
            /[^A-Za-z0-9]/.test(password)
    };


    const score =
        Object.values(checks)
            .filter(Boolean)
            .length;


    return {
        checks,
        score
    };
}


/* =========================================================
   Password Strength UI
========================================================= */

document
    .getElementById('newPassword')
    .addEventListener('input', function () {

        const password = this.value;


        const meter =
            document.getElementById(
                'strengthMeter'
            );


        const fill =
            document.getElementById(
                'strengthFill'
            );


        const label =
            document.getElementById(
                'strengthLabel'
            );


        const t =
            window.translations?.[
                document.documentElement.lang
            ] || {};


        if (!password) {

            meter.style.display = 'none';

            return;
        }


        meter.style.display = 'flex';


        const {
            checks,
            score
        } = evaluatePassword(password);


        const ruleMap = {

            ruleLength:
                checks.length,

            ruleUpper:
                checks.upper,

            ruleLower:
                checks.lower,

            ruleNumber:
                checks.number,

            ruleSymbol:
                checks.symbol
        };


        Object.entries(ruleMap).forEach(
            ([id, passed]) => {

                document
                    .getElementById(id)
                    .classList
                    .toggle(
                        'rule-passed',
                        passed
                    );
            }
        );


        const levels = [

            {
                max: 2,
                className: 'strength-weak',
                label:
                    t.register_strength_weak ||
                    'Weak'
            },

            {
                max: 4,
                className: 'strength-medium',
                label:
                    t.register_strength_medium ||
                    'Medium'
            },

            {
                max: 5,
                className: 'strength-strong',
                label:
                    t.register_strength_strong ||
                    'Strong'
            }
        ];


        const level =
            levels.find(
                l => score <= l.max
            );


        fill.className =
            'strength-fill ' +
            level.className;


        fill.style.width =
            `${(score / 5) * 100}%`;


        label.textContent =
            level.label;


        label.className =
            level.className;
    });


/* =========================================================
   Change Password
========================================================= */

document
    .getElementById('changePasswordForm')
    .addEventListener('submit', async function (event) {

        event.preventDefault();


        const errorBox =
            document.getElementById(
                'pwdError'
            );


        errorBox.style.display = 'none';


        const curr_password =
            document.getElementById(
                'currPassword'
            ).value;


        const new_password =
            document.getElementById(
                'newPassword'
            ).value;


        const new_password_confirmation =
            document.getElementById(
                'newPasswordConfirm'
            ).value;


        const {
            checks,
            score
        } = evaluatePassword(
            new_password
        );


        if (!checks.length || score < 5) {

            errorBox.textContent =
                'Please choose a stronger password (min 8 characters, mixed case, number, symbol).';

            errorBox.style.display =
                'block';

            return;
        }


        if (
            new_password !==
            new_password_confirmation
        ) {

            errorBox.textContent =
                'Passwords do not match.';

            errorBox.style.display =
                'block';

            return;
        }


        try {

            const res = await studentFetch(
                '/api/Student/changePassword',
                {
                    method: 'POST',

                    data: {
                        curr_password,
                        new_password,
                        new_password_confirmation
                    }
                }
            );


            if (!res) return;


            const data = res.data;


            if (data.error) {

                errorBox.textContent =
                    data.error;

                errorBox.style.display =
                    'block';

                return;
            }


            closePopup(
                'changePasswordPopup'
            );

        } catch (error) {

            console.error(
                'Error changing password:',
                error
            );


            errorBox.textContent =
                getApiError(error);


            errorBox.style.display =
                'block';
        }
    });


/* =========================================================
   Enable MFA - Step 1
========================================================= */

document
    .getElementById('openEnableMfaBtn')
    .addEventListener('click', async () => {

        const errorBox =
            document.getElementById(
                'mfaSetupError'
            );


        errorBox.style.display =
            'none';


        document
            .getElementById('mfaCode')
            .value = '';


        document
            .getElementById('qrWrap')
            .innerHTML =
                '<div class="spinner"></div>';


        document
            .getElementById('enableMfaPopup')
            .style.display = 'flex';


        try {

            const res = await studentFetch(
                '/api/Student/setupMfa',
                {
                    method: 'POST'
                }
            );


            if (!res) return;


            const data = res.data;


            if (data.error) {

                errorBox.textContent =
                    data.error;

                errorBox.style.display =
                    'block';

                return;
            }


            document
                .getElementById('qrWrap')
                .innerHTML =
                    `<img src="${data.qr_code}" alt="QR code">`;


            document
                .getElementById('manualSecret')
                .textContent =
                    data.secret;

        } catch (error) {

            console.error(
                'Error starting MFA setup:',
                error
            );


            errorBox.textContent =
                getApiError(
                    error,
                    'Could not start setup. Please try again.'
                );


            errorBox.style.display =
                'block';
        }
    });


/* =========================================================
   Confirm MFA
========================================================= */

document
    .getElementById('confirmMfaForm')
    .addEventListener('submit', async function (event) {

        event.preventDefault();


        const errorBox =
            document.getElementById(
                'mfaSetupError'
            );


        errorBox.style.display =
            'none';


        const code =
            document
                .getElementById('mfaCode')
                .value;


        try {

            const res = await studentFetch(
                '/api/Student/confirmMfa',
                {
                    method: 'POST',

                    data: {
                        code
                    }
                }
            );


            if (!res) return;


            const data = res.data;


            if (data.error) {

                errorBox.textContent =
                    data.error;

                errorBox.style.display =
                    'block';

                return;
            }


            latestRecoveryCodes =
                data.recovery_codes || [];


            closePopup(
                'enableMfaPopup'
            );


            renderRecoveryCodes();


            document
                .getElementById(
                    'recoveryCodesPopup'
                )
                .style.display = 'flex';

        } catch (error) {

            console.error(
                'Error confirming MFA:',
                error
            );


            errorBox.textContent =
                getApiError(error);


            errorBox.style.display =
                'block';
        }
    });


/* =========================================================
   Recovery Codes
========================================================= */

function renderRecoveryCodes() {

    document
        .getElementById('recoveryCodesGrid')
        .innerHTML = latestRecoveryCodes

        .map(
            (code, i) => `
                <div class="recovery-code">
                    <span>${i + 1}.</span>
                    ${code}
                </div>
            `
        )

        .join('');
}


/* =========================================================
   Download Recovery Codes
========================================================= */

document
    .getElementById('downloadCodesBtn')
    .addEventListener('click', () => {

        const text =
            latestRecoveryCodes
                .map(
                    (c, i) =>
                        `${i + 1}. ${c}`
                )
                .join('\n');


        const blob =
            new Blob(
                [text],
                {
                    type: 'text/plain'
                }
            );


        const link =
            document.createElement('a');


        link.href =
            URL.createObjectURL(blob);


        link.download =
            'masar-recovery-codes.txt';


        link.click();


        URL.revokeObjectURL(
            link.href
        );
    });


/* =========================================================
   Copy Recovery Codes
========================================================= */

document
    .getElementById('copyCodesBtn')
    .addEventListener('click', async () => {

        const text =
            latestRecoveryCodes
                .map(
                    (c, i) =>
                        `${i + 1}. ${c}`
                )
                .join('\n');


        await navigator.clipboard.writeText(
            text
        );
    });


/* =========================================================
   Done Recovery Codes
========================================================= */

document
    .getElementById('doneRecoveryBtn')
    .addEventListener('click', () => {

        closePopup(
            'recoveryCodesPopup'
        );


        toggleMfaView(true);
    });


/* =========================================================
   Regenerate Recovery Codes
========================================================= */

document
    .getElementById('openRegenerateBtn')
    .addEventListener('click', () => {

        document
            .getElementById('regenerateForm')
            .reset();


        document
            .getElementById('regenerateError')
            .style.display = 'none';


        document
            .getElementById('regeneratePopup')
            .style.display = 'flex';
    });


document
    .getElementById('regenerateForm')
    .addEventListener('submit', async function (event) {

        event.preventDefault();


        const errorBox =
            document.getElementById(
                'regenerateError'
            );


        errorBox.style.display =
            'none';


        const password =
            document
                .getElementById(
                    'regeneratePassword'
                )
                .value;


        try {

            const res = await studentFetch(
                '/api/Student/regenerateRecoveryCodes',
                {
                    method: 'POST',

                    data: {
                        password
                    }
                }
            );


            if (!res) return;


            const data = res.data;


            if (data.error) {

                errorBox.textContent =
                    data.error;

                errorBox.style.display =
                    'block';

                return;
            }


            latestRecoveryCodes =
                data.recovery_codes || [];


            closePopup(
                'regeneratePopup'
            );


            renderRecoveryCodes();


            document
                .getElementById(
                    'recoveryCodesPopup'
                )
                .style.display = 'flex';

        } catch (error) {

            console.error(
                'Error regenerating codes:',
                error
            );


            errorBox.textContent =
                getApiError(error);


            errorBox.style.display =
                'block';
        }
    });


/* =========================================================
   Disable MFA
========================================================= */

document
    .getElementById('openDisableMfaBtn')
    .addEventListener('click', () => {

        document
            .getElementById('disableMfaForm')
            .reset();


        document
            .getElementById(
                'disableMfaError'
            )
            .style.display = 'none';


        document
            .getElementById(
                'disableMfaPopup'
            )
            .style.display = 'flex';
    });


document
    .getElementById('disableMfaForm')
    .addEventListener('submit', async function (event) {

        event.preventDefault();


        const errorBox =
            document.getElementById(
                'disableMfaError'
            );


        errorBox.style.display =
            'none';


        const password =
            document
                .getElementById(
                    'disablePassword'
                )
                .value;


        try {

            const res = await studentFetch(
                '/api/Student/disableMfa',
                {
                    method: 'POST',

                    data: {
                        password
                    }
                }
            );


            if (!res) return;


            const data = res.data;


            if (data.error) {

                errorBox.textContent =
                    data.error;

                errorBox.style.display =
                    'block';

                return;
            }


            closePopup(
                'disableMfaPopup'
            );


            toggleMfaView(false);

        } catch (error) {

            console.error(
                'Error disabling MFA:',
                error
            );


            errorBox.textContent =
                getApiError(error);


            errorBox.style.display =
                'block';
        }
    });


/* =========================================================
   Initial Load
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    fetchSettings
);


</script>
@endpush
@vite(['resources/css/teacher.css'])
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <span class="eyebrow" data-i18n="teacher_login_eyebrow">Welcome back</span>
            <h1 data-i18n="teacher_login_title">Log in to your teacher account</h1>
            <p data-i18n="teacher_login_subtitle">Enter your email and password to continue.</p>
        </div>

        <div id="loginError" class="auth-error" style="display:none;"></div>

        <form id="loginForm" class="auth-form">
            <label for="email" data-i18n="teacher_login_email_label">Email</label>
            <input type="email" id="email" required placeholder="you@example.com">

            <label for="password" data-i18n="teacher_login_password_label">Password</label>
            <input type="password" id="password" required placeholder="••••••••">

            <button id="submitBtn" type="submit" class="btn-auth" data-i18n="teacher_login_submit">Log in</button>
        </form>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
axios.defaults.withCredentials = true; 

// Show a styled popup (used for MFA step)
function showPopup(html) {
    const popup = document.createElement('div');
    popup.innerHTML = `
        <div class="popup-overlay">
            <div class="popup">
                ${html}
            </div>
        </div>
    `;
    document.body.appendChild(popup);
}

function closePopup() {
    document.querySelector('.popup-overlay')?.remove();
}

function showFormError(el, message) {
    el.textContent = message;
    el.style.display = 'block';
}

async function postLogin(event) {
    event.preventDefault();
    await axios.get('/sanctum/csrf-cookie');

    const errorBox = document.getElementById('loginError');
    errorBox.style.display = 'none';

    const email = document.querySelector('#email').value;
    const password = document.querySelector('#password').value;

    try {
        const res = await axios.post('/api/Teacher/postLogin', { email, password });
        const data = res.data;

        const error = data.error || data.message;
        
        if (error) {
            showFormError(errorBox, error);
            return;
        }

        if (!data.mfa_required) {
            window.location.href = '/Teacher';
        } else {
            localStorage.setItem('mfa_token', data.mfa_token);
            renderMfaPopup();
        }

    } catch (e) {
        const error = e.response?.data?.error || e.response?.data?.message || 'Something went wrong. Please try again.';
        showFormError(errorBox, error);
    }
}

document.querySelector('#loginForm').addEventListener('submit', postLogin);

// MFA code step
function renderMfaPopup() {
    const t = window.translations?.[document.documentElement.lang] || {};

    showPopup(`
        <div id="mfaModel">
            <h3>${t.mfa_title || 'Two-factor verification'}</h3>
            <p class="mfa-subtitle">${t.mfa_subtitle || 'Enter the 6-digit code from your authenticator app.'}</p>
            <div id="mfaError" class="auth-error" style="display:none;"></div>

            <form id="mfaForm" class="auth-form">
                <input type="text" name="code" id="code" maxlength="6" placeholder="000000" autocomplete="one-time-code">
                <button type="submit" class="btn-auth">${t.mfa_verify || 'Verify'}</button>
            </form>

            <button type="button" id="useRecoveryBtn" class="btn-link">${t.mfa_use_recovery || 'Use a recovery code instead'}</button>
            <button type="button" class="btn-link" onclick="closePopup()">${t.mfa_cancel || 'Cancel'}</button>
        </div>
    `);

    document.querySelector('#mfaForm').addEventListener('submit', verifyMfa);
    document.querySelector('#useRecoveryBtn').addEventListener('click', renderRecoveryPopup);
}

async function verifyMfa(event) {
    event.preventDefault();

    const errorBox = document.getElementById('mfaError');
    errorBox.style.display = 'none';

    const code = document.querySelector('#code').value;
    const mfa_token = localStorage.getItem('mfa_token');

    try {
        const res = await axios.post('/api/Teacher/mfa/verify', { mfa_token, code });
        const data = res.data;

        const error = data.error || data.message;
        
        if (error) {
            showFormError(errorBox, error);
            return;
        }

        localStorage.removeItem('mfa_token');
        window.location.href = '/Teacher';

    } catch (e) {
        const error = e.response?.data?.error || e.response?.data?.message || 'Something went wrong. Please try again.';
        showFormError(errorBox, error);
    }
}

// Recovery code step
function renderRecoveryPopup() {
    const t = window.translations?.[document.documentElement.lang] || {};

    showPopup(`
        <div id="recoveryModel">
            <h3>${t.mfa_recovery_title || 'Use a recovery code'}</h3>
            <p class="mfa-subtitle">${t.mfa_recovery_subtitle || 'Enter one of the backup codes you saved when you set up 2FA.'}</p>
            <div id="recoveryError" class="auth-error" style="display:none;"></div>

            <form id="recoveryForm" class="auth-form">
                <input type="text" name="recovery_code" id="recovery_code" placeholder="${t.mfa_recovery_placeholder || 'Recovery code'}">
                <button type="submit" class="btn-auth">${t.mfa_verify || 'Verify'}</button>
            </form>

            <button type="button" id="backToMfaBtn" class="btn-link">${t.mfa_back_to_code || 'Back to verification code'}</button>
            <button type="button" class="btn-link" onclick="closePopup()">${t.mfa_cancel || 'Cancel'}</button>
        </div>
    `);

    document.querySelector('#recoveryForm').addEventListener('submit', verifyRecoveryCode);
    document.querySelector('#backToMfaBtn').addEventListener('click', renderMfaPopup);
}

async function verifyRecoveryCode(event) {
    event.preventDefault();

    const errorBox = document.getElementById('recoveryError');
    errorBox.style.display = 'none';

    const recovery_code = document.querySelector('#recovery_code').value;
    const mfa_token = localStorage.getItem('mfa_token');

    try {
        const res = await axios.post('/api/Teacher/verifyMfaWithRecoveryCode', { mfa_token, recovery_code });
        const data = res.data;

        const error = data.error || data.message;
        
        if (error) {
            showFormError(errorBox, error);
            return;
        }

        localStorage.removeItem('mfa_token');
        window.location.href = '/Teacher';

    } catch (e) {
        const error = e.response?.data?.error || e.response?.data?.message || 'Something went wrong. Please try again.';
        showFormError(errorBox, error);
    }
}
</script>
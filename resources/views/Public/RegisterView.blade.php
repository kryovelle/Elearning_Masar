@extends('layouts.public')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <span class="eyebrow" data-i18n="register_eyebrow">Get started</span>
            <h1 data-i18n="register_title">Create your account</h1>
            <p data-i18n="register_subtitle">Join and start learning at your own pace.</p>
        </div>

        <div id="registerError" class="auth-error" style="display:none;"></div>
        <div id="registerSuccess" class="auth-success" style="display:none;"></div>

        <form id="registerForm" class="auth-form">
            <label for="name" data-i18n="register_name_label">Full name</label>
            <input type="text" id="name" required placeholder="John Doe">

            <label for="email" data-i18n="register_email_label">Email</label>
            <input type="email" id="email" required placeholder="you@example.com">

            <label for="phone" data-i18n="register_phone_label">Phone</label>
            <input type="tel" id="phone" required placeholder="0555 00 00 00">

            <label for="password" data-i18n="register_password_label">Password</label>
            <input type="password" id="password" required placeholder="••••••••">

            <div class="password-strength" id="strengthMeter" style="display:none;">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <span id="strengthLabel"></span>
            </div>

            <ul class="password-rules" id="passwordRules">
                <li id="ruleLength" data-i18n="register_rule_length">At least 8 characters</li>
                <li id="ruleUpper" data-i18n="register_rule_upper">One uppercase letter</li>
                <li id="ruleLower" data-i18n="register_rule_lower">One lowercase letter</li>
                <li id="ruleNumber" data-i18n="register_rule_number">One number</li>
                <li id="ruleSymbol" data-i18n="register_rule_symbol">One symbol</li>
            </ul>

            <label for="passwordConfirm" data-i18n="register_password_confirm_label">Confirm password</label>
            <input type="password" id="passwordConfirm" required placeholder="••••••••">
            <span class="field-error" id="confirmError" style="display:none;" data-i18n="register_password_mismatch">Passwords do not match</span>

            <button id="submitBtn" type="submit" class="btn-auth" data-i18n="register_submit">Create account</button>
        </form>

        <p class="auth-footer-text">
            <span data-i18n="register_have_account">Already have an account?</span>
            <a href="/Public/Login" data-i18n="register_login_link">Log in</a>
        </p>
    </div>
</div>
@endsection


@push('scripts')
<script>
function showFieldError(el, message) {
    el.textContent = message;
    el.style.display = 'block';
}

function hideFieldError(el) {
    el.style.display = 'none';
}

// Evaluate password rules, return { score, checks }
function evaluatePassword(password) {
    const checks = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        symbol: /[^A-Za-z0-9]/.test(password)
    };
    const score = Object.values(checks).filter(Boolean).length;
    return { checks, score };
}

function updateStrengthUI(password) {
    const meter = document.getElementById('strengthMeter');
    const fill = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    const t = window.translations?.[document.documentElement.lang] || {};

    if (!password) {
        meter.style.display = 'none';
        return { checks: {}, score: 0 };
    }
    meter.style.display = 'flex';

    const { checks, score } = evaluatePassword(password);

    const ruleMap = {
        ruleLength: checks.length,
        ruleUpper: checks.upper,
        ruleLower: checks.lower,
        ruleNumber: checks.number,
        ruleSymbol: checks.symbol
    };
    Object.entries(ruleMap).forEach(([id, passed]) => {
        document.getElementById(id).classList.toggle('rule-passed', passed);
    });

    const levels = [
        { max: 2, className: 'strength-weak', label: t.register_strength_weak || 'Weak' },
        { max: 4, className: 'strength-medium', label: t.register_strength_medium || 'Medium' },
        { max: 5, className: 'strength-strong', label: t.register_strength_strong || 'Strong' }
    ];
    const level = levels.find(l => score <= l.max);

    fill.className = 'strength-fill ' + level.className;
    fill.style.width = `${(score / 5) * 100}%`;
    label.textContent = level.label;
    label.className = level.className;

    return { checks, score };
}

document.getElementById('password').addEventListener('input', function () {
    updateStrengthUI(this.value);
    checkPasswordsMatch();
});

document.getElementById('passwordConfirm').addEventListener('input', checkPasswordsMatch);

function checkPasswordsMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('passwordConfirm').value;
    const confirmError = document.getElementById('confirmError');

    if (confirm && password !== confirm) {
        confirmError.style.display = 'block';
        return false;
    }
    confirmError.style.display = 'none';
    return true;
}

async function postRegister(event) {
    event.preventDefault();

    const errorBox = document.getElementById('registerError');
    const successBox = document.getElementById('registerSuccess');
    errorBox.style.display = 'none';
    successBox.style.display = 'none';

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('passwordConfirm').value;

    const { checks, score } = evaluatePassword(password);

    if (!checks.length) {
        showFieldError(errorBox, window.translations?.[document.documentElement.lang]?.register_password_too_short || 'Password must be at least 8 characters.');
        return;
    }

    if (score < 4) {
        showFieldError(errorBox, window.translations?.[document.documentElement.lang]?.register_password_too_weak || 'Please choose a stronger password.');
        return;
    }

    if (password !== passwordConfirm) {
        showFieldError(errorBox, window.translations?.[document.documentElement.lang]?.register_password_mismatch || 'Passwords do not match.');
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;

    try {
        const res = await fetch('/api/Public/postRegister', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
    name, email, phone, password,
    password_confirmation: passwordConfirm
})
        });

        const data = await res.json();
        const error=data.error || data.message;

        if (error) {
            showFieldError(errorBox, error);
            submitBtn.disabled = false;
            return;
        }

        if (!res.ok) {
            throw new Error('Registration failed');
        }

        successBox.textContent = data.successRegister || 'Account created successfully!';
        successBox.style.display = 'block';

        setTimeout(() => {
            window.location.href = '/Public/Login';
        }, 1500);

    } catch (e) {
        console.error(e);
        showFieldError(errorBox, 'Something went wrong. Please try again.');
        submitBtn.disabled = false;
    }
}

document.getElementById('registerForm').addEventListener('submit', postRegister);
</script>
@endpush
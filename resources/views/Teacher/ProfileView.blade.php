@extends('layouts.teacher')

@section('content')
<div class="teacher-settings">
    
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_settings">Loading settings...</p>
    </div>

    <div id="settingsContent" style="display:none;">
        
        <div class="settings-header">
            <h1 data-i18n="teacher_settings_title">Settings</h1>
            <p data-i18n="teacher_settings_subtitle">Manage your profile, security, and account preferences</p>
        </div>

        <!-- Settings Tabs -->
        <div class="settings-tabs">
            <button class="settings-tab active" data-tab="profile" onclick="switchTab('profile')">
                <i class="fas fa-user"></i> <span data-i18n="teacher_tab_profile">Profile</span>
            </button>
            <button class="settings-tab" data-tab="security" onclick="switchTab('security')">
                <i class="fas fa-lock"></i> <span data-i18n="teacher_tab_security">Security</span>
            </button>
            <button class="settings-tab" data-tab="payment" onclick="switchTab('payment')">
                <i class="fas fa-credit-card"></i> <span data-i18n="teacher_tab_payment">Payment</span>
            </button>
        </div>

        <!-- Profile Tab -->
        <div id="tab-profile" class="settings-tab-content active">
            <div class="settings-card">
                <h2 data-i18n="teacher_profile_info">Profile Information</h2>
                
                <div id="profileError" class="settings-error" style="display:none;"></div>
                <div id="profileSuccess" class="settings-success" style="display:none;"></div>

                <form id="profileForm" enctype="multipart/form-data">
                    <div class="settings-avatar-section">
                        <div class="avatar-wrapper">
                            <img id="profilePhoto" src="" alt="Profile Photo">
                            <button type="button" class="avatar-upload-btn" onclick="document.getElementById('photoInput').click()">
                                <i class="fas fa-camera"></i>
                            </button>
                            <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none;">
                        </div>
                        <div class="avatar-info">
                            <p data-i18n="teacher_avatar_help">Click the camera icon to upload a new photo</p>
                            <small data-i18n="teacher_avatar_requirements">JPG, PNG, WEBP (max 5MB)</small>
                        </div>
                    </div>

                    <div class="settings-grid">
                        <div class="form-group">
                            <label for="profileName" data-i18n="teacher_name">Full Name</label>
                            <input type="text" id="profileName" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="profileEmail" data-i18n="teacher_email">Email</label>
                            <input type="email" id="profileEmail" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="profilePhone" data-i18n="teacher_phone">Phone</label>
                            <input type="text" id="profilePhone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="profileBio" data-i18n="teacher_bio">Bio</label>
                            <textarea id="profileBio" name="bio" rows="3" class="form-control" placeholder="Tell students about yourself..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-settings" data-i18n="teacher_save_profile">Save Profile</button>
                </form>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="tab-security" class="settings-tab-content">
            <div class="settings-card">
                <h2 data-i18n="teacher_change_password">Change Password</h2>
                
                <div id="passwordError" class="settings-error" style="display:none;"></div>
                <div id="passwordSuccess" class="settings-success" style="display:none;"></div>

                <form id="passwordForm">
                    <div class="settings-grid">
                        <div class="form-group">
                            <label for="currentPassword" data-i18n="teacher_current_password">Current Password</label>
                            <input type="password" id="currentPassword" name="curr_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword" data-i18n="teacher_new_password">New Password</label>
                            <input type="password" id="newPassword" name="new_password" class="form-control" required>
                            <small data-i18n="teacher_password_requirements">Min 8 chars with uppercase, lowercase, number and symbol</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" data-i18n="teacher_confirm_password">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-settings" data-i18n="teacher_change_password_btn">Change Password</button>
                </form>
            </div>

            <!-- MFA Section -->
            <div class="settings-card">
                <h2 data-i18n="teacher_mfa_title">Two-Factor Authentication</h2>
                <p data-i18n="teacher_mfa_description">Add an extra layer of security to your account</p>

                <div id="mfaError" class="settings-error" style="display:none;"></div>
                <div id="mfaSuccess" class="settings-success" style="display:none;"></div>

                <div id="mfaStatus">
                    <!-- Dynamically populated -->
                </div>
            </div>
        </div>

        <!-- Payment Tab -->
        <div id="tab-payment" class="settings-tab-content">
            <div class="settings-card">
                <h2 data-i18n="teacher_payment_settings">Payment Settings</h2>
                <p data-i18n="teacher_payment_settings_sub">Configure your payment information for receiving student payments</p>

                <div id="paymentError" class="settings-error" style="display:none;"></div>
                <div id="paymentSuccess" class="settings-success" style="display:none;"></div>

                <form id="paymentForm">
                    <div class="settings-grid">
                        <div class="form-group">
                            <label for="ccpNumber" data-i18n="teacher_ccp_number">CCP Number</label>
                            <input type="text" id="ccpNumber" name="ccp_number" class="form-control" placeholder="e.g. 123456789">
                            <small data-i18n="teacher_ccp_help">Your CCP account number for receiving payments</small>
                        </div>
                        <div class="form-group">
                            <label for="ccpName" data-i18n="teacher_ccp_name">CCP Account Name</label>
                            <input type="text" id="ccpName" name="ccp_name" class="form-control" placeholder="e.g. Karim Benyahia">
                            <small data-i18n="teacher_ccp_name_help">The name on your CCP account</small>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-settings" data-i18n="teacher_save_payment_settings">Save Payment Settings</button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- MFA Setup Modal -->
<div id="mfaModal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h2 data-i18n="teacher_mfa_setup_title">Setup Two-Factor Authentication</h2>
            <button class="modal-close" onclick="closeMfaModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="mfaModalError" class="modal-error" style="display:none;"></div>
            <div id="mfaModalContent">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<!-- Recovery Codes Modal -->
<div id="recoveryModal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h2 data-i18n="teacher_recovery_codes">Recovery Codes</h2>
            <button class="modal-close" onclick="closeRecoveryModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="recoveryContent">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentUser = {};
let mfaSecret = null;

function isMfaEnabled(user) {
    return user.mfa_enabled === true || user.mfa_enabled === 1 || user.mfa_enabled === '1';
}

async function loadSettings() {
    const loadingState = document.getElementById('loadingState');
    const settingsContent = document.getElementById('settingsContent');

    loadingState.style.display = 'block';
    settingsContent.style.display = 'none';

    try {
        const response = await teacherFetch('/api/Teacher/getSettingsData');
        currentUser = response.data.user || {};

        populateProfileForm(currentUser);
        populatePaymentForm(currentUser);
        renderMfaStatus(currentUser);
        loadPhoto(currentUser.photo_url);

        loadingState.style.display = 'none';
        settingsContent.style.display = 'block';

        const currentLang = document.documentElement.lang || 'en';
        if (typeof window.applyLanguage === 'function') {
            window.applyLanguage(currentLang);
        }

    } catch (error) {
        console.error('Error loading settings:', error);
        loadingState.style.display = 'none';
        showError('Failed to load settings.');
    }
}

function populateProfileForm(user) {
    document.getElementById('profileName').value = user.name || '';
    document.getElementById('profileEmail').value = user.email || '';
    document.getElementById('profilePhone').value = user.phone || '';
    document.getElementById('profileBio').value = user.bio || '';
}

function populatePaymentForm(user) {
    document.getElementById('ccpNumber').value = user.ccp_number || '';
    document.getElementById('ccpName').value = user.ccp_name || '';
}

function loadPhoto(photoUrl) {
    const img = document.getElementById('profilePhoto');
    if (photoUrl) {
        img.src = photoUrl;
    } else {
        img.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentUser.name || 'User') + '&background=3454d1&color=fff&size=120';
    }
}

function renderMfaStatus(user) {
    const container = document.getElementById('mfaStatus');
    const enabled = isMfaEnabled(user);

    if (enabled) {
        container.innerHTML = `
            <div class="mfa-status enabled">
                <div class="mfa-status-icon"><i class="fas fa-check-circle" style="color:#2e7d32;"></i></div>
                <div class="mfa-status-info">
                    <strong data-i18n="teacher_mfa_enabled">Two-Factor Authentication is ENABLED</strong>
                    <p data-i18n="teacher_mfa_enabled_desc">Your account is protected with 2FA</p>
                </div>
                <div class="mfa-status-actions">
                    <button class="btn-mfa-regenerate" onclick="regenerateRecoveryCodes()">
                        <i class="fas fa-sync"></i> <span data-i18n="teacher_regenerate_codes">Regenerate Codes</span>
                    </button>
                    <button class="btn-mfa-disable" onclick="disableMfa()">
                        <i class="fas fa-times"></i> <span data-i18n="teacher_disable_mfa">Disable 2FA</span>
                    </button>
                </div>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="mfa-status disabled">
                <div class="mfa-status-icon"><i class="fas fa-exclamation-circle" style="color:#e65100;"></i></div>
                <div class="mfa-status-info">
                    <strong data-i18n="teacher_mfa_disabled">Two-Factor Authentication is DISABLED</strong>
                    <p data-i18n="teacher_mfa_disabled_desc">Your account is not protected with 2FA</p>
                </div>
                <button class="btn-mfa-enable" onclick="setupMfa()">
                    <i class="fas fa-shield-alt"></i> <span data-i18n="teacher_enable_mfa">Enable 2FA</span>
                </button>
            </div>
        `;
    }
}

// Profile Form Submit
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const errorBox = document.getElementById('profileError');
    const successBox = document.getElementById('profileSuccess');
    errorBox.style.display = 'none';
    successBox.style.display = 'none';

    const submitBtn = this.querySelector('.btn-save-settings');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData(this);

    try {
        const response = await teacherFetch('/api/Teacher/postSettingsData', {
            method: 'POST',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.success) {
            successBox.textContent = response.data.success;
            successBox.style.display = 'block';
            await loadSettings(); // refresh currentUser + avatar from server (photo_url only known after upload)
            setTimeout(() => successBox.style.display = 'none', 3000);
        }
    } catch (error) {
        errorBox.textContent = error.response?.data?.message || 'An error occurred.';
        errorBox.style.display = 'block';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Password Form Submit
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const errorBox = document.getElementById('passwordError');
    const successBox = document.getElementById('passwordSuccess');
    errorBox.style.display = 'none';
    successBox.style.display = 'none';

    const submitBtn = this.querySelector('.btn-save-settings');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Changing...';

    const data = {
        curr_password: document.getElementById('currentPassword').value,
        new_password: document.getElementById('newPassword').value,
        new_password_confirmation: document.getElementById('confirmPassword').value
    };

    try {
        const response = await teacherFetch('/api/Teacher/changePassword', {
            method: 'POST',
            data: data,
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            successBox.textContent = response.data.success;
            successBox.style.display = 'block';
            this.reset();
            setTimeout(() => successBox.style.display = 'none', 3000);
        }
    } catch (error) {
        errorBox.textContent = error.response?.data?.error || 'An error occurred.';
        errorBox.style.display = 'block';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Payment Form Submit
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const errorBox = document.getElementById('paymentError');
    const successBox = document.getElementById('paymentSuccess');
    errorBox.style.display = 'none';
    successBox.style.display = 'none';

    const submitBtn = this.querySelector('.btn-save-settings');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    // postSettingsData requires name/email/phone on every call —
    // reuse the values already loaded into currentUser so this
    // endpoint doesn't 422 when only editing payment info.
    const data = {
        name: currentUser.name || '',
        email: currentUser.email || '',
        phone: currentUser.phone || '',
        bio: currentUser.bio || '',
        ccp_number: document.getElementById('ccpNumber').value,
        ccp_name: document.getElementById('ccpName').value
    };

    try {
        const response = await teacherFetch('/api/Teacher/postSettingsData', {
            method: 'POST',
            data: data,
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            successBox.textContent = response.data.success;
            successBox.style.display = 'block';
            currentUser.ccp_number = data.ccp_number;
            currentUser.ccp_name = data.ccp_name;
            setTimeout(() => successBox.style.display = 'none', 3000);
        }
    } catch (error) {
        errorBox.textContent = error.response?.data?.message || 'An error occurred.';
        errorBox.style.display = 'block';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// MFA Functions
async function setupMfa() {
    try {
        const response = await teacherFetch('/api/Teacher/setupMfa', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        mfaSecret = response.data.secret;
        const qrCode = response.data.qr_code;

        document.getElementById('mfaModalContent').innerHTML = `
            <div class="mfa-setup">
                <p data-i18n="teacher_mfa_scan_qr">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                <div class="qr-container">
                    <img src="${qrCode}" alt="QR Code">
                </div>
                <p><strong data-i18n="teacher_mfa_secret">Secret Key:</strong> <code>${mfaSecret}</code></p>
                <p data-i18n="teacher_mfa_enter_code">Enter the 6-digit code from your authenticator app:</p>

                <form id="mfaConfirmForm">
                    <div class="form-group">
                        <input type="text" id="mfaCode" maxlength="6" inputmode="numeric" pattern="[0-9]*" class="form-control" placeholder="000000" required>
                    </div>
                    <button type="submit" class="btn-submit" data-i18n="teacher_verify_mfa">Verify & Enable</button>
                </form>
            </div>
        `;

        document.getElementById('mfaModal').style.display = 'flex';

        document.getElementById('mfaConfirmForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            await confirmMfa();
        });

    } catch (error) {
        console.error('Error setting up MFA:', error);
        alert('Failed to setup MFA.');
    }
}

async function confirmMfa() {
    const errorBox = document.getElementById('mfaModalError');
    errorBox.style.display = 'none';

    const code = document.getElementById('mfaCode').value;

    try {
        const response = await teacherFetch('/api/Teacher/confirmMfa', {
            method: 'POST',
            data: { code: code },
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            closeMfaModal();
            showRecoveryCodes(response.data.recovery_codes);
            loadSettings();
        }
    } catch (error) {
        errorBox.textContent = error.response?.data?.error || 'Invalid code. Please try again.';
        errorBox.style.display = 'block';
    }
}

function showRecoveryCodes(codes) {
    const container = document.getElementById('recoveryContent');
    container.innerHTML = `
        <div class="recovery-codes">
            <p data-i18n="teacher_recovery_codes_important">Save these recovery codes in a safe place. Each code can only be used once.</p>
            <div class="codes-grid">
                ${codes.map(code => `<span class="recovery-code">${code}</span>`).join('')}
            </div>
            <button class="btn-submit" onclick="closeRecoveryModal()" data-i18n="teacher_copied_saved">I've Saved These</button>
        </div>
    `;
    document.getElementById('recoveryModal').style.display = 'flex';
}

async function regenerateRecoveryCodes() {
    const password = prompt('Enter your password to regenerate recovery codes:');
    if (!password) return;

    try {
        const response = await teacherFetch('/api/Teacher/regenerateRecoveryCodes', {
            method: 'POST',
            data: { password: password },
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            closeRecoveryModal();
            showRecoveryCodes(response.data.recovery_codes);
        }
    } catch (error) {
        alert(error.response?.data?.error || 'Failed to regenerate codes.');
    }
}

async function disableMfa() {
    const password = prompt('Enter your password to disable 2FA:');
    if (!password) return;

    try {
        const response = await teacherFetch('/api/Teacher/disableMfa', {
            method: 'POST',
            data: { password: password },
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            alert('MFA disabled successfully.');
            loadSettings();
        }
    } catch (error) {
        alert(error.response?.data?.error || 'Failed to disable MFA.');
    }
}

function closeMfaModal() {
    document.getElementById('mfaModal').style.display = 'none';
}

function closeRecoveryModal() {
    document.getElementById('recoveryModal').style.display = 'none';
}

function switchTab(tabName) {
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.tab === tabName);
    });

    document.querySelectorAll('.settings-tab-content').forEach(content => {
        content.classList.toggle('active', content.id === 'tab-' + tabName);
    });
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'settings-error-global';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.querySelector('.teacher-settings').prepend(errorDiv);
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('mfaModal').addEventListener('click', function(e) {
        if (e.target === this) closeMfaModal();
    });
    document.getElementById('recoveryModal').addEventListener('click', function(e) {
        if (e.target === this) closeRecoveryModal();
    });
    loadSettings();
});
</script>
@endpush
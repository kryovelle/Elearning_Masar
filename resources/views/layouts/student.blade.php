<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Masar — Student Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

@vite(['resources/css/student.css'])



</head>
<body>

<header class="student-header">
  <nav class="student-nav">
    <div class="logo-area">
                <div class="logo-icon"><i class="fas fa-square-root-variable"></i></div>
                <div class="logo-text">Ma<span>sar</span></div>
            </div>
   <div class="nav-links">
    <a href="{{ route('student.dashboard') }}" 
       data-i18n="student_nav_dashboard" 
       class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('student.courses') }}" 
       data-i18n="student_nav_courses" 
       class="nav-link {{ request()->routeIs('student.courses') ? 'active' : '' }}">
        Courses
    </a>
    <a href="{{ route('student.payments') }}" 
       data-i18n="student_nav_payments" 
       class="nav-link {{ request()->routeIs('student.payments') ? 'active' : '' }}">
        Payments
    </a>
    <a href="{{ route('student.profile') }}" 
       data-i18n="student_nav_profile" 
       class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
        Profile
    </a>
</div>
    <div class="nav-right">
      <div class="lang-switch" role="group" aria-label="Language selector">
        <button class="lang-btn active" data-lang="en" type="button" onclick="switchLanguage('en')">EN</button>
        <button class="lang-btn" data-lang="fr" type="button" onclick="switchLanguage('fr')">FR</button>
        <button class="lang-btn" data-lang="ar" type="button" onclick="switchLanguage('ar')">AR</button>
      </div>
      <button id="logoutBtn" class="btn-logout" data-i18n="student_logout">Log out</button>
    </div>
  </nav>
</header>

<main>
@yield('content')
</main>

<!-- Only load translations once -->
@vite(['resources/js/translations.js'])

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- Translation initialization script -->
<script>
/* =========================================================
   Student API Fetch
========================================================= */

async function studentFetch(url, options = {}) {
    try {
        const res = await axios({
            url,
            method: options.method || 'get',
            data: options.data || undefined,
            headers: {
                'Accept': 'application/json',
                ...(options.headers || {})
            }
        });

        return res;

    } catch (e) {

        if (e.response?.status === 401) {
            window.location.href = '/Public/Login';
            return null;
        }

        throw e;
    }
}

/* =========================================================
   Logout Functionality
========================================================= */

// Create logout dropdown
function setupLogoutDropdown() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (!logoutBtn) return;

    // Check if dropdown already exists
    if (document.querySelector('.logout-dropdown')) return;

    // Create dropdown container
    const dropdown = document.createElement('div');
    dropdown.className = 'logout-dropdown';
    dropdown.innerHTML = `
        <div class="logout-dropdown-menu">
            <div class="logout-option" data-action="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span data-i18n="student_logout_this_device">Logout from this device</span>
            </div>
            <div class="logout-option" data-action="logoutAll">
                <i class="fas fa-sign-out-alt"></i>
                <span data-i18n="student_logout_all_devices">Logout from all devices</span>
            </div>
            <div class="logout-option cancel" data-action="cancel">
                <i class="fas fa-times"></i>
                <span data-i18n="student_cancel">Cancel</span>
            </div>
        </div>
    `;

    // Append dropdown to the nav-right
    const navRight = logoutBtn.closest('.nav-right');
    if (navRight) {
        navRight.style.position = 'relative';
        navRight.appendChild(dropdown);
    }

    // Toggle dropdown on button click
    logoutBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('active');
    });

    // Handle dropdown option clicks
    dropdown.querySelectorAll('.logout-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.getAttribute('data-action');
            
            if (action === 'logout') {
                handleLogout('logout');
            } else if (action === 'logoutAll') {
                handleLogout('logoutAll');
            } else if (action === 'cancel') {
                dropdown.classList.remove('active');
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== logoutBtn) {
            dropdown.classList.remove('active');
        }
    });
}

// Handle logout with confirm dialog
async function handleLogout(action) {
    const message = action === 'logoutAll' 
        ? 'Are you sure you want to logout from ALL devices?' 
        : 'Are you sure you want to logout?';
    
    if (!confirm(message)) return;

    const endpoint = action === 'logoutAll' 
        ? '/api/Public/logoutAll' 
        : '/api/Public/logout';

    try {
        // Show loading state
        const logoutBtn = document.getElementById('logoutBtn');
        const originalText = logoutBtn.textContent;
        logoutBtn.textContent = 'Logging out...';
        logoutBtn.disabled = true;

        const response = await studentFetch(endpoint, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response && response.data.success ) {
            window.location.href = '/Public/Login'}

    } catch (error) {
        console.error('Logout error:', error);
        alert('An error occurred during logout.');
    } finally {
        // Reset button state
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.textContent = originalText;
            logoutBtn.disabled = false;
        }
        // Close dropdown
        document.querySelector('.logout-dropdown')?.classList.remove('active');
    }
}

// Ensure translation functions are available
document.addEventListener('DOMContentLoaded', function() {
    // Setup logout dropdown
    setupLogoutDropdown();
    
    // If translations haven't loaded, wait for them
    if (typeof window.switchLanguage === 'undefined') {
        console.warn('Translations not loaded yet');
    } else {
        // Apply saved language preference
        const savedLang = localStorage.getItem('preferred_language') || 'en';
        window.switchLanguage(savedLang);
    }
});
</script>

@stack('scripts')

</body>
</html>
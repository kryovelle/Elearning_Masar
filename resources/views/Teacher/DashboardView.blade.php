@extends('layouts.teacher')

@section('content')
<div class="teacher-dashboard">
    
    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_dashboard">Loading dashboard...</p>
    </div>

    <!-- Dashboard Content -->
    <div id="dashboardContent" style="display:none;">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
           <h1>
            <span data-i18n="teacher_dashboard_welcome">Welcome back,</span>
            <span id="teacherName"></span>
        </h1>
            <p data-i18n="teacher_dashboard_subtitle">Here's what's happening with your courses today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="totalCourses">0</span>
                    <span class="stat-label" data-i18n="teacher_stat_total_courses">Total Courses</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #388e3c;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="activeStudents">0</span>
                    <span class="stat-label" data-i18n="teacher_stat_active_students">Active Students</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="pendingPayments">0</span>
                    <span class="stat-label" data-i18n="teacher_stat_pending_payments">Pending Payments</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8eaf6; color: #3949ab;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="totalRevenue">0 DA</span>
                    <span class="stat-label" data-i18n="teacher_stat_total_revenue">Total Revenue</span>
                </div>
            </div>
        </div>

        <!-- Pending Payments Section -->
        <div class="pending-section">
            <div class="section-header">
    <div>
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f57c00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:8px;">
                <path d="M12 9v4"/>
                <path d="M12 17h.01"/>
                <circle cx="12" cy="12" r="10"/>
            </svg>
            <span data-i18n="teacher_pending_title">Pending Payments (Needs Action)</span>
        </h2>
        <p data-i18n="teacher_pending_subtitle">Review and confirm student payments</p>
    </div>
    <button class="btn-view-all" onclick="viewAllPending()" data-i18n="teacher_view_all">View All Pending</button>
</div>

            <div id="pendingPaymentsTable">
                <div class="table-wrapper">
                    <table class="pending-table">
                        <thead>
                            <tr>
                                <th data-i18n="teacher_pending_student">Student</th>
                                <th data-i18n="teacher_pending_course">Course</th>
                                <th data-i18n="teacher_pending_date">Date</th>
                                <th data-i18n="teacher_pending_action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pendingPaymentsBody">
                            <!-- Dynamically populated -->
                        </tbody>
                    </table>
                </div>
                <div id="noPendingPayments" class="no-data" style="display:none;">
                    <i class="fas fa-check-circle"></i>
                    <p data-i18n="teacher_no_pending">All caught up! No pending payments to review.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 data-i18n="teacher_quick_actions">Quick Actions</h3>
            <div class="action-buttons-dash">
                <a href="{{ route('teacher.courses') }}" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span data-i18n="teacher_action_add_course">Add New Course</span>
                </a>
                <a href="{{ route('teacher.students') }}" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span data-i18n="teacher_action_view_students">View Students</span>
                </a>
                <a href="{{ route('teacher.payments') }}" class="action-btn">
                    <i class="fas fa-credit-card"></i>
                    <span data-i18n="teacher_action_manage_payments">Manage Payments</span>
                </a>
                <a href="{{ route('teacher.profile') }}" class="action-btn">
                    <i class="fas fa-cog"></i>
                    <span data-i18n="teacher_action_settings">Settings</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// Fetch dashboard data
async function loadDashboardData() {
    const loadingState = document.getElementById('loadingState');
    const dashboardContent = document.getElementById('dashboardContent');
    
    loadingState.style.display = 'block';
    dashboardContent.style.display = 'none';

    try {
        const response = await teacherFetch('/api/Teacher/getTeacherDashboardData', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = response.data;

        
        
        // Update teacher name
        if (data.teacher && data.teacher.name) {
            document.getElementById('teacherName').textContent = data.teacher.name;
        }

        // Update stats
        const stats = data.stats_cards || {};
        document.getElementById('totalCourses').textContent = stats.total_courses_count || 0;
        document.getElementById('activeStudents').textContent = stats.active_students_count || 0;
        document.getElementById('pendingPayments').textContent = stats.pending_payments_count || 0;
        document.getElementById('totalRevenue').textContent = (stats.total_revenue || 0).toLocaleString() + ' DA';

        // Render pending payments
        renderPendingPayments(data.pending_payments || []);

        // Show content
        loadingState.style.display = 'none';
        dashboardContent.style.display = 'block';

    } catch (error) {
        console.error('Error loading dashboard data:', error);
        loadingState.style.display = 'none';
        showError('Failed to load dashboard data. Please refresh the page.');
    }
}

// Render pending payments table
function renderPendingPayments(payments) {
    const tbody = document.getElementById('pendingPaymentsBody');
    const noData = document.getElementById('noPendingPayments');
    
    if (!payments || payments.length === 0) {
        tbody.innerHTML = '';
        noData.style.display = 'block';
        return;
    }

    noData.style.display = 'none';

    const dict = window.translations?.[document.documentElement.lang] || {};
    const reviewText = dict.teacher_pending_review || 'Review';

    tbody.innerHTML = payments.map(payment => `
        <tr>
            <td class="student-name">
                <span class="student-avatar">${getInitials(payment.student_name)}</span>
                ${payment.student_name}
            </td>
            <td>${payment.course_title}</td>
            <td>${formatDate(payment.date)}</td>
            <td>
                <button class="btn-review" onclick="reviewPayment(${payment.payment_id})">
                    <i class="fas fa-eye"></i> ${reviewText}
                </button>
            </td>
        </tr>
    `).join('');
}

// Helper: Get initials from name
function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}

// Helper: Format date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Handle "View All Pending" button
function viewAllPending() {
    // Redirect to payments page with pending filter
    window.location.href = '/Teacher/Payments?filter=pending';
}

// Review a specific payment
async function reviewPayment(paymentId) {
    window.location.href='Teacher/Payments';
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'dashboard-error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.querySelector('.teacher-dashboard').prepend(errorDiv);
}

// Load data when page is ready
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});
</script>
@endpush
@extends('layouts.student')

@section('content')
<div class="dashboard-page">

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="student_loading">Loading your dashboard...</p>
    </div>

    <div id="dashboardContent" style="display:none;">

        <div class="dashboard-greeting">
            <h1><span id="greetingName"></span> <span>👋</span></h1>
            <p data-i18n="student_greeting_sub">Here's where you left off.</p>
        </div>

        <div class="dashboard-section">
            <h2 data-i18n="student_recently_enrolled">Recently Enrolled</h2>

            <div class="enrollment-list" id="enrollmentList"></div>

            <div id="emptyEnrollments" class="empty-state" style="display:none;">
                <i class="fas fa-graduation-cap"></i>
                <h3 data-i18n="student_no_enrollments">No courses yet</h3>
                <p data-i18n="student_no_enrollments_sub">Browse the catalogue and enroll in your first course.</p>
                <a href="/Public/Courses" class="btn-primary" data-i18n="student_browse_courses">Browse courses</a>
            </div>

            <a href="/Student/Courses" class="btn-view-all" id="viewAllLink" style="display:none;">
                <span data-i18n="student_view_all_courses">View All My Courses</span> <i class="fas fa-arrow-right"></i>
            </a>
        </div>

    </div>
</div>
@endsection


@push('scripts')
<script>
function statusBadge(status) {
    const t = window.translations?.[document.documentElement.lang] || {};
    const s = (status || '').toLowerCase();

    if (s === 'approved') {
        return { icon: 'fa-check-circle', className: 'status-active', label: t.student_status_active || 'Active' };
    }
    if (s === 'pending') {
        return { icon: 'fa-hourglass-half', className: 'status-pending', label: t.student_status_pending || 'Pending' };
    }
    if (s === 'rejected') {
        return { icon: 'fa-times-circle', className: 'status-rejected', label: t.student_status_rejected || 'Rejected' };
    }
    return { icon: 'fa-circle', className: 'status-unknown', label: status || '' };
}

function statusSubtext(status, teacherNote) {
    const t = window.translations?.[document.documentElement.lang] || {};
    const s = (status || '').toLowerCase();

    if (s === 'pending') {
        return t.student_pending_note || 'Awaiting payment verification';
    }
    if (s === 'rejected') {
        const base = t.student_rejected_note || 'Receipt unclear — please resend';
        return teacherNote ? `${base} (${teacherNote})` : base;
    }
    return t.student_active_note || 'Enrolled course';
}

function statusSubtext(status, teacherNote) {
    const t = window.translations?.[document.documentElement.lang] || {};
    const s = (status || '').toLowerCase();

    if (s === 'pending') {
        return t.student_pending_note || 'Awaiting payment verification';
    }
    if (s === 'rejected') {
        const base = t.student_rejected_note || 'Receipt unclear — please resend';
        return teacherNote ? `${base} (${teacherNote})` : base;
    }
    return t.student_active_note || 'Enrolled course';
}

async function fetchDashboard() {
    try {
        const res = await window.studentFetch('/api/Student/getDashboardData');
        if (!res) return; // already redirected to login on 401

        const data = res.data;

        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('dashboardContent').style.display = 'block';

        document.getElementById('greetingName').textContent =
            (window.translations?.[document.documentElement.lang]?.student_hi || 'Hi') + ' ' + (data.user?.name || '');

        renderEnrollments(data.recently_enrolled || []);
    } catch (e) {
        const error=e?.response.data.error|| e?.response.data.message || e || 'An error occured please try again!';
        console.error( error);
        document.getElementById('loadingState').style.display = 'none';
    }
}

function renderEnrollments(enrollments) {
    const list = document.getElementById('enrollmentList');
    const empty = document.getElementById('emptyEnrollments');
    const viewAll = document.getElementById('viewAllLink');

    if (!enrollments.length) {
        list.innerHTML = '';
        empty.style.display = 'block';
        viewAll.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    viewAll.style.display = 'inline-flex';

    list.innerHTML = enrollments.map(e => {
        const badge = statusBadge(e.status);
        const subtext = statusSubtext(e.status, e.teacher_note);

        return `
            <div class="enrollment-item">
                <div class="enrollment-main">
                    <h4>${e.course_title || ''}</h4>
                    <p class="enrollment-subtext">${subtext}</p>
                </div>
                <div class="enrollment-status ${badge.className}">
                    <i class="fas ${badge.icon}"></i> ${badge.label}
                </div>
            </div>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', fetchDashboard);
</script>
@endpush
@extends('layouts.teacher')

@section('content')
<div class="teacher-students">
    
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_students">Loading students...</p>
    </div>

    <div id="studentsContent" style="display:none;">
        
        <div class="students-header">
            <div>
                <h1 data-i18n="teacher_students_title">Students</h1>
                <p data-i18n="teacher_students_subtitle">Manage and view all enrolled students</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="students-filters">
            <div class="filter-group">
                <label for="searchStudent" data-i18n="teacher_search_student">Search</label>
                <input type="text" id="searchStudent" placeholder="Search by name or email..." data-i18n="teacher_search_student_placeholder">
            </div>
            <div class="filter-actions">
                <button class="btn-filter" onclick="applyFilters()" data-i18n="teacher_filter">Filter</button>
                <button class="btn-clear" onclick="clearFilters()" data-i18n="teacher_clear">Clear</button>
            </div>
        </div>

        <!-- Students Table -->
        <div class="students-table-wrapper">
            <table class="students-table">
                <thead>
                    <tr>
                        <th data-i18n="teacher_id">ID</th>
                        <th data-i18n="teacher_name">Name</th>
                        <th data-i18n="teacher_email">Email</th>
                        <th data-i18n="teacher_joined">Joined</th>
                        <th data-i18n="teacher_enrolled_courses">Enrolled Courses</th>
                        <th data-i18n="teacher_actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <!-- Dynamically populated -->
                </tbody>
            </table>
            <div id="noStudents" class="no-data" style="display:none;">
                <i class="fas fa-users"></i>
                <p data-i18n="teacher_no_students">No students found.</p>
            </div>
        </div>

    </div>
</div>

<!-- Student Detail Modal -->
<div id="studentModal" class="modal-overlay" style="display:none;">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2 id="studentModalTitle" data-i18n="teacher_student_details">Student Details</h2>
            <button class="modal-close" onclick="closeStudentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="studentModalContent">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allStudents = [];
let filteredStudents = [];

async function loadStudents() {
    const loadingState = document.getElementById('loadingState');
    const studentsContent = document.getElementById('studentsContent');

    loadingState.style.display = 'block';
    studentsContent.style.display = 'none';

    try {
        const response = await teacherFetch('/api/Teacher/getStudents');
        allStudents = response.data.students || [];
        filteredStudents = [...allStudents];
        renderStudents(filteredStudents);

        loadingState.style.display = 'none';
        studentsContent.style.display = 'block';

        applyCurrentLanguage();

    } catch (error) {
        console.error('Error loading students:', error);
        loadingState.style.display = 'none';
        showError('Failed to load students.');
    }
}

function applyCurrentLanguage() {
    const currentLang = document.documentElement.lang || 'en';
    if (typeof window.applyLanguage === 'function') {
        window.applyLanguage(currentLang);
    }
}

function renderStudents(students) {
    const tbody = document.getElementById('studentsTableBody');
    const noData = document.getElementById('noStudents');

    if (!students || students.length === 0) {
        tbody.innerHTML = '';
        noData.style.display = 'block';
        return;
    }

    noData.style.display = 'none';

    tbody.innerHTML = students.map(student => {
        const approvedEnrollments = student.enrollments?.filter(e => e.status === 'approved') || [];
        const approvedCount = approvedEnrollments.length;

        return `
            <tr>
                <td>#${student.id}</td>
                <td>
                    <div class="student-name-cell">
                        <span class="student-avatar">${getInitials(student.name)}</span>
                        <span>${student.name || 'Unknown'}</span>
                    </div>
                </td>
                <td>${student.email || '-'}</td>
                <td>${formatDate(student.created_at)}</td>
                <td>
                    <span class="enrollment-badge">${approvedCount}</span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-view-student" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye"></i> <span data-i18n="teacher_view">View</span>
                        </button>
                        <button class="btn-block-student" onclick="toggleBlockStudent(${student.id}, ${student.is_blocked ? 'true' : 'false'})">
                            <i class="fas ${student.is_blocked ? 'fa-unlock' : 'fa-lock'}"></i>
                            <span data-i18n="${student.is_blocked ? 'teacher_unblock' : 'teacher_block'}">
                                ${student.is_blocked ? 'Unblock' : 'Block'}
                            </span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    applyCurrentLanguage();
}

function applyFilters() {
    const searchTerm = document.getElementById('searchStudent').value.toLowerCase().trim();

    if (!searchTerm) {
        filteredStudents = [...allStudents];
    } else {
        filteredStudents = allStudents.filter(student => {
            const name = (student.name || '').toLowerCase();
            const email = (student.email || '').toLowerCase();
            return name.includes(searchTerm) || email.includes(searchTerm);
        });
    }

    renderStudents(filteredStudents);
}

function clearFilters() {
    document.getElementById('searchStudent').value = '';
    filteredStudents = [...allStudents];
    renderStudents(filteredStudents);
}

async function viewStudent(studentId) {
    try {
        const response = await teacherFetch(`/api/Teacher/getStudentById?student_id=${studentId}`);
        const student = response.data.student;

        if (!student) {
            alert('Student not found.');
            return;
        }

        renderStudentDetail(student);
        document.getElementById('studentModal').style.display = 'flex';

        applyCurrentLanguage();

    } catch (error) {
        console.error('Error loading student details:', error);
        const message = error.response?.data?.error || 'Failed to load student details.';
        alert(message);
    }
}

function renderStudentDetail(student) {
    const container = document.getElementById('studentModalContent');
    const currentLang = document.documentElement.lang || 'en';
    const dict = window.translations?.[currentLang] || {};

    const statusLabels = {
        pending: dict.teacher_status_pending || 'Pending',
        approved: dict.teacher_status_approved || 'Approved',
        rejected: dict.teacher_status_rejected || 'Rejected'
    };

    const enrollments = student.enrollments || [];
    const approvedEnrollments = enrollments.filter(e => e.status === 'approved');

    container.innerHTML = `
        <div class="student-detail-header">
            <div class="student-detail-avatar">${getInitials(student.name)}</div>
            <div class="student-detail-info">
                <h3>${student.name || 'Unknown'}</h3>
                <p><i class="fas fa-envelope"></i> ${student.email || '-'}</p>
                <p><i class="fas fa-calendar-alt"></i> ${dict.teacher_joined || 'Joined'}: ${formatDate(student.created_at)}</p>
                <p><i class="fas fa-graduation-cap"></i> ${dict.teacher_enrolled_courses || 'Enrolled Courses'}: ${approvedEnrollments.length}</p>
                <span class="student-status ${student.is_blocked ? 'status-blocked' : 'status-active'}">
                    ${student.is_blocked ? (dict.teacher_blocked || 'Blocked') : (dict.teacher_active || 'Active')}
                </span>
            </div>
        </div>

        <div class="student-enrollments-section">
            <h4 data-i18n="teacher_enrollment_history">Enrollment History</h4>
            ${enrollments.length === 0 ? `
                <p class="no-enrollments" data-i18n="teacher_no_enrollments">No enrollments found.</p>
            ` : `
                <div class="table-wrapper">
                    <table class="enrollments-table">
                        <thead>
                            <tr>
                                <th data-i18n="teacher_course">Course</th>
                                <th data-i18n="teacher_amount">Amount</th>
                                <th data-i18n="teacher_enrolled_at">Enrolled At</th>
                                <th data-i18n="teacher_status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${enrollments.map(enrollment => `
                                <tr>
                                    <td>${enrollment.course?.title || '-'}</td>
                                    <td>${enrollment.payment?.amount || 0} DA</td>
                                    <td>${formatDate(enrollment.created_at)}</td>
                                    <td>
                                        <span class="enrollment-status status-${enrollment.status}">
                                            ${statusLabels[enrollment.status] || enrollment.status || 'Pending'}
                                        </span>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `}
        </div>

        <div class="student-payments-summary">
            <h4 data-i18n="teacher_payments_summary">Payments Summary</h4>
            <div class="summary-stats">
                <div class="summary-stat">
                    <span class="stat-label" data-i18n="teacher_total_paid">Total Paid</span>
                    <span class="stat-value">${calculateTotalPaid(enrollments)} DA</span>
                </div>
                <div class="summary-stat">
                    <span class="stat-label" data-i18n="teacher_pending_payments">Pending Payments</span>
                    <span class="stat-value">${enrollments.filter(e => e.status === 'pending').length}</span>
                </div>
                <div class="summary-stat">
                    <span class="stat-label" data-i18n="teacher_approved_courses">Approved Courses</span>
                    <span class="stat-value">${approvedEnrollments.length}</span>
                </div>
            </div>
        </div>
    `;
}

function calculateTotalPaid(enrollments) {
    let total = 0;
    enrollments.forEach(enrollment => {
        if (enrollment.status === 'approved' && enrollment.payment) {
            total += parseFloat(enrollment.payment.amount || 0);
        }
    });
    return total.toFixed(0);
}

async function toggleBlockStudent(studentId, isBlocked) {
    const currentLang = document.documentElement.lang || 'en';
    const dict = window.translations?.[currentLang] || {};

    const confirmMessage = isBlocked
        ? dict.teacher_confirm_unblock || 'Unblock this student?'
        : dict.teacher_confirm_block || 'Block this student?';

    if (!confirm(confirmMessage)) return;

    try {
        const response = await teacherFetch('/api/Teacher/toggleBlockStudent', {
            method: 'POST',
            data: { student_id: studentId, block: !isBlocked },
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.data.success) {
            alert(response.data.success);
            loadStudents();
        }
    } catch (error) {
        console.error('Error toggling block status:', error);
        alert('Failed to update student status.');
    }
}

function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'students-error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.querySelector('.teacher-students').prepend(errorDiv);
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('studentModal').addEventListener('click', function(e) {
        if (e.target === this) closeStudentModal();
    });
    loadStudents();
});
</script>
@endpush
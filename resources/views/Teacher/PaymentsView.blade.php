@extends('layouts.teacher')

@section('content')
<div class="teacher-payments">
    
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_payments">Loading payments...</p>
    </div>

    <div id="paymentsContent" style="display:none;">
        
        <div class="payments-header">
            <div>
                <h1 data-i18n="teacher_payments_title">Payments</h1>
                <p data-i18n="teacher_payments_subtitle">Manage and review student payments</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="payments-filters">
            <div class="filter-group">
                <label for="courseFilter" data-i18n="teacher_course">Course</label>
                <select id="courseFilter">
                    <option value="" data-i18n="teacher_all_courses">All Courses</option>
                    <!-- Populated by JS -->
                </select>
            </div>
            <div class="filter-group">
                <label for="studentFilter" data-i18n="teacher_student">Student</label>
                <input type="text" id="studentFilter" placeholder="Search student..." data-i18n="teacher_search_student" style="width:90%">
            </div>
            <div class="filter-group">
                <label for="statusFilter" data-i18n="teacher_status">Status</label>
                <select id="statusFilter">
                    <option value="" data-i18n="teacher_all_status">All Status</option>
                    <option value="pending" data-i18n="teacher_status_pending">Pending</option>
                    <option value="approved" data-i18n="teacher_status_approved">Approved</option>
                    <option value="rejected" data-i18n="teacher_status_rejected">Rejected</option>
                </select>
            </div>
            <button class="btn-filter" onclick="applyFilters()" data-i18n="teacher_filter">Filter</button>
            <button class="btn-clear" onclick="clearFilters()" data-i18n="teacher_clear">Clear</button>
        </div>

        <!-- Payments List -->
        <div id="paymentsList">
            <div id="paymentsContainer" class="payments-container">
                <!-- Dynamically populated -->
            </div>
            <div id="noPayments" class="no-data" style="display:none;">
                <i class="fas fa-credit-card"></i>
                <p data-i18n="teacher_no_payments">No payments found.</p>
            </div>
        </div>

    </div>
</div>

<!-- Receipt Zoom Modal -->
<div id="receiptModal" class="modal-overlay" style="display:none;">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2 data-i18n="teacher_receipt_preview">Receipt Preview</h2>
            <button class="modal-close" onclick="closeReceiptModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="receiptImageContainer" class="receipt-image-container">
                <img id="receiptFullImage" src="" alt="Receipt">
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="noteModal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h2 data-i18n="teacher_add_note">Add Teacher Note</h2>
            <button class="modal-close" onclick="closeNoteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="noteError" class="modal-error" style="display:none;"></div>
            <form id="noteForm">
                <input type="hidden" id="notePaymentId">
                <div class="form-group">
                    <label for="teacherNote" data-i18n="teacher_note_label">Note</label>
                    <textarea id="teacherNote" rows="4" class="form-control" placeholder="Enter your note about this payment..." required></textarea>
                </div>
                <button type="submit" class="btn-submit" data-i18n="teacher_save_note">Save Note</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allPayments = [];
let courses = [];
let selectedCourseId = null;

async function loadPayments() {
    const loadingState = document.getElementById('loadingState');
    const paymentsContent = document.getElementById('paymentsContent');

    loadingState.style.display = 'block';
    paymentsContent.style.display = 'none';

    try {
        selectedCourseId = getCourseIdFromUrl();

        await loadCoursesForFilter();

        // Preselect only if the course actually exists in the loaded list —
        // guards against a silently-empty course list (e.g. backend error)
        // leaving the dropdown stuck on "All Courses" with no feedback.
        if (selectedCourseId) {
            const select = document.getElementById('courseFilter');
            const matchExists = Array.from(select.options)
                .some(opt => String(opt.value) === String(selectedCourseId));

            if (matchExists) {
                select.value = String(selectedCourseId);
            } else {
                console.warn('Course from URL not found in course list — course dropdown may have failed to load.');
            }
        }

        await fetchPayments();

        loadingState.style.display = 'none';
        paymentsContent.style.display = 'block';

        const currentLang = document.documentElement.lang || 'en';
        if (typeof window.applyLanguage === 'function') {
            window.applyLanguage(currentLang);
        }

    } catch (error) {
        console.error('Error loading payments:', error);
        loadingState.style.display = 'none';
        showError('Failed to load payments.');
    }
}

function getCourseIdFromUrl() {
    const parts = window.location.pathname.split('/');
    const lastPart = parts[parts.length - 1];
    if (lastPart && !isNaN(lastPart) && lastPart.trim() !== '' && lastPart !== 'Payments') {
        return parseInt(lastPart, 10);
    }
    return null;
}

async function loadCoursesForFilter() {
    try {
        const response = await teacherFetch('/api/Teacher/getTeacherCourses');
        courses = response.data.courses || [];

        const select = document.getElementById('courseFilter');
        select.innerHTML = '<option value="" data-i18n="teacher_all_courses">All Courses</option>';

        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = String(course.id);
            option.textContent = course.title;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading courses for filter:', error);
        showError('Failed to load your courses list — the course filter may be incomplete.');
    }
}

async function fetchPayments() {
    const courseId = document.getElementById('courseFilter').value;
    const studentName = document.getElementById('studentFilter').value;
    const status = document.getElementById('statusFilter').value;

    let url = '/api/Teacher/getPayments?';
    const params = [];
    if (courseId) params.push(`course_id=${courseId}`);
    if (studentName) params.push(`student_name=${encodeURIComponent(studentName)}`);
    if (status) params.push(`filter=${status}`);
    url += params.join('&');

    const response = await teacherFetch(url);
    allPayments = response.data.payments || [];
    renderPayments(allPayments);
}

function renderPayments(payments) {
    const container = document.getElementById('paymentsContainer');
    const noData = document.getElementById('noPayments');

    if (!payments || payments.length === 0) {
        container.innerHTML = '';
        noData.style.display = 'block';
        return;
    }

    noData.style.display = 'none';

    const currentLang = document.documentElement.lang || 'en';
    const dict = window.translations?.[currentLang] || {};

    const statusLabels = {
        pending: dict.teacher_status_pending || 'Pending',
        approved: dict.teacher_status_approved || 'Approved',
        rejected: dict.teacher_status_rejected || 'Rejected'
    };

    const statusColors = {
        pending: 'status-pending',
        approved: 'status-approved',
        rejected: 'status-rejected'
    };

    container.innerHTML = payments.map(payment => {
        const status = payment.status || 'pending';

        return `
        <div class="payment-card">
            <div class="payment-card-header">
                <div class="payment-student-info">
                    <span class="payment-student-avatar">${getInitials(payment.student)}</span>
                    <div>
                        <strong class="payment-student-name">${payment.student || 'Unknown'}</strong>
                        <span class="payment-course">${payment.course || 'No course'}</span>
                    </div>
                </div>
                <span class="payment-status ${statusColors[status] || 'status-pending'}">
                    ${statusLabels[status] || status}
                </span>
            </div>

            <div class="payment-card-body">
                <div class="payment-details">
                    <div class="payment-detail-item">
                        <span class="payment-detail-label"><i class="fas fa-calendar"></i> <span data-i18n="teacher_date">Date</span></span>
                        <span class="payment-detail-value">${formatDate(payment.date)}</span>
                    </div>
                    <div class="payment-detail-item">
                        <span class="payment-detail-label"><i class="fas fa-money-bill-wave"></i> <span data-i18n="teacher_amount">Amount</span></span>
                        <span class="payment-detail-value">${payment.amount || 0} DA</span>
                    </div>
                </div>

                ${payment.receipt_image_url ? `
                    <div class="payment-receipt">
                        <img src="${payment.receipt_image_url}" alt="Receipt" onclick="openReceiptModal('${payment.receipt_image_url}')">
                        <button class="btn-zoom" onclick="openReceiptModal('${payment.receipt_image_url}')">
                            <i class="fas fa-search-plus"></i> <span data-i18n="teacher_zoom">Zoom</span>
                        </button>
                    </div>
                ` : `
                    <div class="payment-receipt no-receipt">
                        <i class="fas fa-file-image"></i>
                        <span data-i18n="teacher_no_receipt">No receipt uploaded</span>
                    </div>
                `}

               <div class="payment-notes-row" style="display:flex; gap:16px; margin-top:8px;">
                    <div class="payment-student-note" style="flex:1;">
                        <strong data-i18n="teacher_student_note">Student Note:</strong>
                        <p>${payment.student_note || '—'}</p>
                    </div>

                    <div class="payment-teacher-note" style="flex:1;">
                        <strong data-i18n="teacher_teacher_note">Teacher Note:</strong>
                        <p>${payment.teacher_note || '—'}</p>
                    </div>
                </div>
            </div>

            <div class="payment-card-actions">
                ${status !== 'approved' ? `
                    <button class="btn-approve" onclick="changePaymentStatus(${payment.payment_id}, 'approved')">
                        <i class="fas fa-check"></i> <span data-i18n="teacher_approve">Approve</span>
                    </button>
                ` : ''}
                ${status !== 'rejected' ? `
                    <button class="btn-reject" onclick="changePaymentStatus(${payment.payment_id}, 'rejected')">
                        <i class="fas fa-times"></i> <span data-i18n="teacher_reject">Reject</span>
                    </button>
                ` : ''}
                <button class="btn-note" onclick="openNoteModal(${payment.payment_id})">
                    <i class="fas fa-sticky-note"></i> <span data-i18n="teacher_add_note">Add Note</span>
                </button>
            </div>
        </div>
        `;
    }).join('');
}

function applyFilters() {
    fetchPayments();
}

function clearFilters() {
    document.getElementById('courseFilter').value = selectedCourseId ? String(selectedCourseId) : '';
    document.getElementById('studentFilter').value = '';
    document.getElementById('statusFilter').value = '';
    fetchPayments();
}

async function changePaymentStatus(paymentId, status) {
    const currentLang = document.documentElement.lang || 'en';
    const dict = window.translations?.[currentLang] || {};

    const confirmMessage = status === 'approved'
        ? dict.teacher_confirm_approve || 'Approve this payment?'
        : dict.teacher_confirm_reject || 'Reject this payment?';

    if (!confirm(confirmMessage)) return;

    try {
        const response = await teacherFetch('/api/Teacher/changePaymentStatus', {
            method: 'POST',
            data: { payment_id: paymentId, status: status },
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.data.success) {
            alert(response.data.success);
            fetchPayments();
        }
    } catch (error) {
        console.error('Error changing payment status:', error);
        alert('Failed to update payment status.');
    }
}

function openNoteModal(paymentId) {
    document.getElementById('notePaymentId').value = paymentId;
    document.getElementById('teacherNote').value = '';
    document.getElementById('noteError').style.display = 'none';
    document.getElementById('noteModal').style.display = 'flex';
}

function closeNoteModal() {
    document.getElementById('noteModal').style.display = 'none';
}

document.getElementById('noteForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const errorBox = document.getElementById('noteError');
    errorBox.style.display = 'none';

    const paymentId = document.getElementById('notePaymentId').value;
    const teacherNote = document.getElementById('teacherNote').value;

    if (!teacherNote.trim()) {
        errorBox.textContent = 'Please enter a note.';
        errorBox.style.display = 'block';
        return;
    }

    try {
        const response = await teacherFetch('/api/Teacher/addTeacherNote', {
            method: 'POST',
            data: { payment_id: paymentId, teacher_note: teacherNote },
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.data.success) {
            alert(response.data.success);
            closeNoteModal();
            fetchPayments();
        }
    } catch (error) {
        console.error('Error adding note:', error);
        errorBox.textContent = 'Failed to add note. Please try again.';
        errorBox.style.display = 'block';
    }
});

function openReceiptModal(imageUrl) {
    document.getElementById('receiptFullImage').src = imageUrl;
    document.getElementById('receiptModal').style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('receiptModal').style.display = 'none';
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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'payments-error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.querySelector('.teacher-payments').prepend(errorDiv);
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('receiptModal').addEventListener('click', function(e) {
        if (e.target === this) closeReceiptModal();
    });
    document.getElementById('noteModal').addEventListener('click', function(e) {
        if (e.target === this) closeNoteModal();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    loadPayments();
});
</script>
@endpush
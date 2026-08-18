@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts.student')
@section('content')
<div class="payments-page">

    <div class="payments-header">
        <h1 data-i18n="student_my_payments">My Payments</h1>

        <div class="payments-filters">
            <input type="text" id="courseSearch" placeholder="..." class="payments-search">
            <select id="statusFilter" class="filter-select">
                <option value="" data-i18n="student_filter_all">All</option>
                <option value="approved" data-i18n="student_status_active">Approved</option>
                <option value="pending" data-i18n="student_status_pending">Pending</option>
                <option value="rejected" data-i18n="student_status_rejected">Rejected</option>
            </select>
        </div>
    </div>

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="student_loading_payments">Loading your payments...</p>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fas fa-receipt"></i>
        <h3 data-i18n="student_no_payments">No payments found</h3>
    </div>

    <div class="payments-table-wrap" id="paymentsTableWrap" style="display:none;">
        <table class="payments-table">
            <thead>
                <tr>
                    <th data-i18n="student_col_course">Course</th>
                    <th data-i18n="student_col_amount">Amount</th>
                    <th data-i18n="student_col_date">Date</th>
                    <th data-i18n="student_col_status">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="paymentsTableBody"></tbody>
        </table>
    </div>

</div>

<!-- View popup -->
<div id="viewPopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_payment_details">Payment Details</h3>
            <button class="popup-close" onclick="closePopup('viewPopup')">&times;</button>
        </div>
        <div class="popup-body" id="viewPopupBody"></div>
        <button class="btn-auth" onclick="closePopup('viewPopup')" data-i18n="student_close">Close</button>
    </div>
</div>

<!-- Edit popup -->
<div id="editPopup" class="popup-overlay" style="display:none;">
    <div class="popup">
        <div class="popup-header">
            <h3 data-i18n="student_update_payment">Update Payment</h3>
            <button class="popup-close" onclick="closePopup('editPopup')">&times;</button>
        </div>
        <div class="popup-body">
            <p><strong data-i18n="student_col_course">Course</strong>: <span id="editCourseName"></span></p>
            <div id="editRejectionNote" class="edit-rejection-note" style="display:none;"></div>

            <div id="editError" class="auth-error" style="display:none;"></div>

            <form id="editPaymentForm" class="auth-form">
                <label data-i18n="student_upload_receipt">Upload New Receipt</label>
                <input type="file" id="receiptFile" accept="image/*" required>

                <label for="editNote" data-i18n="student_note_optional">Note (optional)</label>
                <input type="text" id="editNote" placeholder="">

                <div class="popup-actions">
                    <button type="button" class="btn-cancel" onclick="closePopup('editPopup')" data-i18n="student_cancel">Cancel</button>
                    <button type="submit" class="btn-auth" data-i18n="student_resubmit">Resubmit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
let allPayments = [];

function statusBadge(status) {
    const t = window.translations?.[document.documentElement.lang] || {};
    const s = (status || '').toLowerCase();

    if (s === 'approved') {
        return {
            icon: 'fa-check-circle',
            className: 'status-active',
            label: t.student_status_active || 'Approved'
        };
    }

    if (s === 'pending') {
        return {
            icon: 'fa-hourglass-half',
            className: 'status-pending',
            label: t.student_status_pending || 'Pending'
        };
    }

    if (s === 'rejected') {
        return {
            icon: 'fa-times-circle',
            className: 'status-rejected',
            label: t.student_status_rejected || 'Rejected'
        };
    }

    return {
        icon: 'fa-circle',
        className: 'status-unknown',
        label: status || ''
    };
}


async function fetchPayments() {
    try {
        const res = await studentFetch('/api/Student/getPayments');

        if (!res) return;

        const data = res.data;
        allPayments = data.payments || [];

        document.getElementById('loadingState').style.display = 'none';
        applyFilters();

    } catch (e) {
        const error =
            e?.response?.data?.error ||
            e?.response?.data?.message ||
            'Error fetching payments';

        console.error(error);

        document.getElementById('loadingState').style.display = 'none';

        // There is no showPlayerError()
        // Display the error in the loading area instead.
        const loadingState = document.getElementById('loadingState');

        if (loadingState) {
            loadingState.innerHTML = `<p>${error}</p>`;
            loadingState.style.display = 'block';
        }
    }
}


function applyFilters() {
    const search = document.getElementById('courseSearch')
        .value
        .trim()
        .toLowerCase();

    const status = document.getElementById('statusFilter').value;

    let filtered = allPayments;

    if (search) {
        filtered = filtered.filter(p =>
            (p.course || '').toLowerCase().includes(search)
        );
    }

    if (status) {
        filtered = filtered.filter(p =>
            (p.status || '').toLowerCase() === status
        );
    }

    renderPayments(filtered);
}


function renderPayments(payments) {
    const tableWrap = document.getElementById('paymentsTableWrap');
    const empty = document.getElementById('emptyState');
    const tbody = document.getElementById('paymentsTableBody');

    if (!payments.length) {
        tableWrap.style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    tableWrap.style.display = 'block';

    const t = window.translations?.[document.documentElement.lang] || {};

    tbody.innerHTML = payments.map(p => {
        const badge = statusBadge(p.status);

        const canEdit = ['pending', 'rejected'].includes(
            (p.status || '').toLowerCase()
        );

        return `
            <tr>
                <td>${p.course || ''}</td>

                <td>${p.amount || 0} DA</td>

                <td>${formatDate(p.date)}</td>

                <td>
                    <span class="table-status ${badge.className}">
                        <i class="fas ${badge.icon}"></i>
                        ${badge.label}
                    </span>
                </td>

                <td class="table-actions">

                    <button
                        class="btn-table-action"
                        onclick="openViewPopup(${p.payment_id})"
                    >
                        ${t.student_view || 'View'}
                    </button>

                    ${
                        canEdit
                            ? `
                                <button
                                    class="btn-table-action btn-table-edit"
                                    onclick="openEditPopup(
                                        ${p.payment_id},
                                        '${(p.course || '').replace(/'/g, "\\'")}'
                                    )"
                                >
                                    ${t.student_edit || 'Edit'}
                                </button>
                              `
                            : ''
                    }

                </td>
            </tr>
        `;
    }).join('');
}


function formatDate(dateStr) {
    if (!dateStr) return '—';

    const d = new Date(dateStr);

    return d.toLocaleDateString(undefined, {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}


document.getElementById('courseSearch')
    .addEventListener('input', applyFilters);

document.getElementById('statusFilter')
    .addEventListener('change', applyFilters);


function closePopup(id) {
    document.getElementById(id).style.display = 'none';
}


/* =========================
   View popup
========================= */

async function openViewPopup(paymentId) {

    const body = document.getElementById('viewPopupBody');

    body.innerHTML =
        '<div class="spinner" style="margin:2rem auto;"></div>';

    document.getElementById('viewPopup').style.display = 'flex';

    try {

        const res = await studentFetch(
            `/api/Student/getPaymentById?payment_id=${paymentId}`
        );

        if (!res) return;

        const data = res.data;
        const p = data.payment;

        if (!p) {
            body.innerHTML = `<p>Payment not found.</p>`;
            return;
        }

        const badge = statusBadge(p.status);

        const t =
            window.translations?.[document.documentElement.lang] || {};

        body.innerHTML = `
            <p>
                <strong>${t.student_col_course || 'Course'}:</strong>
                ${p.course || ''}
            </p>

            <p>
                <strong>${t.student_col_amount || 'Amount'}:</strong>
                ${p.amount || 0} DA
            </p>

            <p>
                <strong>${t.student_submitted || 'Submitted'}:</strong>
                ${formatDate(p.submitted_at)}
            </p>

            <p>
                <strong>${t.student_col_status || 'Status'}:</strong>

                <span class="table-status ${badge.className}">
                    <i class="fas ${badge.icon}"></i>
                    ${badge.label}
                </span>
            </p>

            ${
                p.receipt_image_url
                    ? `
                        <p>
                            <strong>
                                ${t.student_receipt || 'Receipt'}:
                            </strong>
                            <br>

                            <img
                                src="${p.receipt_image_url}"
                                class="receipt-thumb"
                            >
                        </p>
                      `
                    : ''
            }

            <p>
                <strong>${t.student_your_note || 'Your note'}:</strong>
                ${p.ur_note || '—'}
            </p>

            <p>
                <strong>${t.student_teacher_note || 'Teacher note'}:</strong>
                ${p.teacher_note || '—'}
            </p>
        `;

    } catch (e) {

        const error =
            e?.response?.data?.error ||
            e?.response?.data?.message ||
            'Error loading payment';

        console.error(error);

        // OLD VERSION BEHAVIOR:
        // show the error inside the popup itself.
        body.innerHTML = `<p>${error}</p>`;
    }
}


/* =========================
   Edit popup
========================= */

let editingPaymentId = null;


async function openEditPopup(paymentId, courseName) {

    editingPaymentId = paymentId;

    document.getElementById('editCourseName').textContent = courseName;

    document.getElementById('editError').style.display = 'none';

    document.getElementById('receiptFile').value = '';

    document.getElementById('editNote').value = '';

    document.getElementById('editRejectionNote').style.display = 'none';

    document.getElementById('editPopup').style.display = 'flex';

    try {

        const res = await studentFetch(
            `/api/Student/getPaymentById?payment_id=${paymentId}`
        );

        if (!res) return;

        const data = res.data;

        if (
            data.payment &&
            (data.payment.status || '').toLowerCase() === 'rejected' &&
            data.payment.teacher_note
        ) {

            const box =
                document.getElementById('editRejectionNote');

            box.textContent = data.payment.teacher_note;

            box.style.display = 'block';
        }

    } catch (e) {

        const error =
            e?.response?.data?.error ||
            e?.response?.data?.message ||
            'Error loading payment for edit';

        console.error(error);

        // OLD VERSION BEHAVIOR:
        // show error in the edit form's error box.
        const errorBox =
            document.getElementById('editError');

        errorBox.textContent = error;
        errorBox.style.display = 'block';
    }
}


/* =========================
   Edit payment submission
========================= */

document
    .getElementById('editPaymentForm')
    .addEventListener('submit', async function (event) {

        event.preventDefault();

        const errorBox =
            document.getElementById('editError');

        errorBox.style.display = 'none';

        const file =
            document.getElementById('receiptFile').files[0];

        if (!file) {

            errorBox.textContent =
                'Please select a receipt image.';

            errorBox.style.display = 'block';

            return;
        }

        const formData = new FormData();

        formData.append(
            'payment_id',
            editingPaymentId
        );

        formData.append(
            'receipt_image',
            file
        );

        formData.append(
            'student_note',
            document.getElementById('editNote').value
        );

        try {

            const res = await studentFetch(
                '/api/Student/editPayment',
                {
                    'method': 'POST',
                    'data': formData
                }
            );

            if (!res) return;

            const data = res.data;

            closePopup('editPopup');

            fetchPayments();

        } catch (e) {
            console.log(e.response);

            const error =
                e?.response?.data?.error ||
                e?.response?.data?.message ||e||
                'Error resubmitting payment';

            console.error(error);

            // OLD VERSION BEHAVIOR:
            // show submission errors inside #editError.
            errorBox.textContent = error;

            errorBox.style.display = 'block';
        }
    });


document.addEventListener(
    'DOMContentLoaded',
    fetchPayments
);

</script>
@endpush
@extends('layouts.public')

@section('content')
<div class="course-detail-page">

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_courses">Loading courses...</p>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fas fa-book-open"></i>
        <h3 data-i18n="course_not_found">Course not found</h3>
        <p data-i18n="try_adjusting_filters">Try adjusting your filters or search terms.</p>
    </div>

    <div id="courseDetailContent" style="display:none;">

        <div class="course-detail-grid">
            <div class="course-detail-image" id="courseImageWrap"></div>

            <div class="course-detail-info">
                <h1 id="courseTitle"></h1>

                <div class="course-detail-meta">
                    <div class="meta-item">
                        <strong id="coursePrice"></strong>
                        <span data-i18n="price_unit">/ course</span>
                    </div>
                    <div class="meta-item">
                        <strong id="courseDuration"></strong>
                        <span data-i18n="course_detail_weeks">weeks</span>
                    </div>
                </div>

                <button class="btn-primary"style="width:150px" id="enrollBtn" data-i18n="course_detail_enroll_now">Enroll Now</button>
            </div>
        </div>

        <div class="course-detail-section">
            <h2 data-i18n="course_detail_description_title" style="color:#1a6bc4;">Description</h2>
            <p id="courseDescription"></p>
        </div>

        <div class="course-detail-section">
            <h2 data-i18n="course_detail_modules_title">Modules</h2>
            <div class="module-list" id="moduleList"></div>
        </div>

        <div class="course-detail-section">
            <h2 data-i18n="course_detail_payment_title">Payment Info Preview</h2>
            <div class="payment-preview">
                <div class="payment-item">
                    <span data-i18n="footer_ccp_label">CCP:</span> <span id="ccpNumber"></span>
                </div>
                <div class="payment-item">
                    <span data-i18n="footer_name_label">Name:</span> <span id="ccpName"></span>
                </div>
                <p class="payment-note" data-i18n="course_detail_payment_note">Pay via CCP after registration</p>
            </div>
        </div>

    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
axios.defaults.withCredentials = true;
// Handle course image fallback (same helper as courses.blade)
function handleCourseImgError(img) {
    const fallbackIcon = img.getAttribute('data-fallback-icon') || 'fa-square-root-variable';
    const parent = img.parentElement;
    parent.innerHTML = `<i class="fas ${fallbackIcon} fa-2x"></i>`;
}

// Get course id from the URL path (/courses/{id})
function getCourseIdFromPath() {
    const parts = window.location.pathname.split('/').filter(Boolean);
    return parts[parts.length - 1];
}

// Fetch course detail
async function fetchCourseDetail() {
    const id = getCourseIdFromPath();
    showLoading(true);

    try {
        const response = await fetch(`/api/Public/getCourseDataById?id=${id}`);
        const data = await response.json();
        const course = data.course;
        if (!course || !course.id) {
            showLoading(false);
            showEmptyState();
            return;
        }

        showLoading(false);
        renderCourseDetail(course,data);
    } catch (error) {
        console.error('Error fetching course details:', error);
        showLoading(false);
        showEmptyState();
    }
}

// Render course detail
function renderCourseDetail(c,data) {
    const ccp_number=data.ccp_number;
        const ccp_name=data.ccp_name;
    document.getElementById('courseDetailContent').style.display = 'block';

    const imageWrap = document.getElementById('courseImageWrap');
    imageWrap.innerHTML = c.cover_image_url
        ? `<img src="${c.cover_image_url}" data-fallback-icon="${c.icon || 'fa-square-root-variable'}" style="width:100%;height:100%;object-fit:cover;border-radius:28px;" onerror="handleCourseImgError(this)">`
        : `<i class="fas ${c.icon || 'fa-square-root-variable'} fa-2x"></i>`;

    document.getElementById('courseTitle').textContent = c.title || '';
    document.getElementById('coursePrice').textContent = `${c.price || 0} DA`;
    document.getElementById('courseDuration').textContent = c.duration_weeks || '';
    document.getElementById('courseDescription').textContent = c.description || '';

    const translations = window.translations?.[document.documentElement.lang] || {};
    const lockedText = translations?.course_detail_locked || 'Locked';
    const previewText = translations?.course_detail_preview_only || 'Preview only';

const modules=data.modules;
    const moduleList = document.getElementById('moduleList');
    moduleList.innerHTML = (modules || []).map((m, index) => `
        <div class="module-item">
            <div class="module-icon"><i class="fas fa-lock"></i></div>
            <div class="module-title">${m.order_index || index + 1}. ${m.title || ''}</div>
            <div class="module-status">${index === 0 ? previewText : lockedText}</div>
        </div>
    `).join('');

    document.getElementById('ccpNumber').textContent = c.ccp_number_override || ccp_number || '_';
    document.getElementById('ccpName').textContent = c.ccp_name_override || ccp_name  || '_';

    document.getElementById('enrollBtn').onclick = function () {
    showEnrollmentModal(c.title, c.price, c.id);
    };
}

// Show/hide loading
function showLoading(show) {
    document.getElementById('loadingState').style.display = show ? 'block' : 'none';
    if (show) {
        document.getElementById('courseDetailContent').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
    }
}

// Show empty/not-found state
function showEmptyState() {
    document.getElementById('emptyState').style.display = 'block';
    document.getElementById('courseDetailContent').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    fetchCourseDetail();
});



// Show enrollment modal
function showEnrollmentModal(courseTitle, coursePrice, courseId) {
    // Check if modal already exists, remove if so
    const existingModal = document.getElementById('enrollmentModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal HTML
    const modalHTML = `
        <div id="enrollmentModal" class="enrollment-modal-overlay">
            <div class="enrollment-modal">
                <div class="enrollment-modal-header">
                    <h2 data-i18n="enroll_title">Enroll in Course</h2>
                    <button class="enrollment-modal-close" onclick="closeEnrollmentModal()">&times;</button>
                </div>
                <div class="enrollment-modal-body">
                    <!-- Message Container -->
                    <div id="enrollmentMessage" class="enrollment-message" style="display:none;"></div>
                    
                    <div class="enrollment-course-info">
                        <p><strong data-i18n="enroll_course">Course:</strong> <span id="enrollCourseTitle">${courseTitle}</span></p>
                        <p><strong data-i18n="enroll_price">Price:</strong> <span id="enrollCoursePrice">${coursePrice}</span></p>
                    </div>
                    
                    <form id="enrollmentForm" enctype="multipart/form-data">
                        <div class="enrollment-form-group">
                            <label for="receiptImage" data-i18n="enroll_upload_receipt">Upload Payment Receipt</label>
                            <input type="file" id="receiptImage" name="receipt_image_url" accept="image/*" required>
                            <small data-i18n="enroll_receipt_note">Upload a photo of your CCP payment receipt (jpeg,png,jpg,webp,pdf)</small>
                        </div>
                        
                        <div class="enrollment-form-group">
                            <label for="studentNote" data-i18n="enroll_note">Note (Optional)</label>
                            <textarea id="studentNote" name="student_note" rows="3" placeholder="Any additional information..."></textarea>
                        </div>
                        
                        <div class="enrollment-form-actions">
                            <button type="button" class="btn-secondary" onclick="closeEnrollmentModal()" data-i18n="enroll_cancel">Cancel</button>
                            <button type="submit" class="btn-primary" id="enrollSubmitBtn" data-i18n="enroll_submit">Submit Enrollment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Append modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Handle form submission
    document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleEnrollmentSubmit(courseId);
    });
}

// Handle enrollment submission
async function handleEnrollmentSubmit(courseId) {
    const form = document.getElementById('enrollmentForm');
    const formData = new FormData(form);
    formData.append('course_id', courseId);

    // Get elements
    const submitBtn = document.getElementById('enrollSubmitBtn');
    const messageContainer = document.getElementById('enrollmentMessage');
    const originalText = submitBtn.textContent;

    // Clear previous messages
    messageContainer.style.display = 'none';
    messageContainer.className = 'enrollment-message';
    messageContainer.textContent = '';

    try {
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        // Send to API
        const res = await axios('/api/Public/enroll', {
            method: 'POST',
            data: formData
        });


        const data = res.data;

        // Show success message
        if (data.success) {
            messageContainer.className = 'enrollment-message enrollment-success';
            messageContainer.textContent = data.success || 'Enrollment submitted successfully! You will receive a confirmation soon.';
            messageContainer.style.display = 'block';
            
            // Hide form on success
            form.style.display = 'none';
            
            // Optionally close modal after delay
            setTimeout(() => {
                closeEnrollmentModal();
            }, 3000);
        }

    } catch (e) {
        if (e.response?.status === 401) {
            window.location.href = '/Public/Login';
            return null;
        }

        const error = e?.response?.data?.error || e?.response?.data?.message || e  ||'Error submitting enrollment';
        console.error( error);
        // Show error message
        messageContainer.className = 'enrollment-message enrollment-error';
        messageContainer.textContent = error;
        messageContainer.style.display = 'block';
    } finally {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// Close enrollment modal
function closeEnrollmentModal() {
    const modal = document.getElementById('enrollmentModal');
    if (modal) {
        modal.remove();
    }
}

window.handleCourseImgError = handleCourseImgError;
</script>
@endpush
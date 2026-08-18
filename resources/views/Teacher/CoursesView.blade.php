@extends('layouts.teacher')

@section('content')
<div class="teacher-courses">
    
    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_courses">Loading courses...</p>
    </div>

    <!-- Courses Content -->
    <div id="coursesContent" style="display:none;">
        
        <!-- Header -->
        <div class="courses-header">
            <div>
                <h1 data-i18n="teacher_courses_title">My Courses</h1>
                <p data-i18n="teacher_courses_subtitle">Manage your courses and their content</p>
            </div>
            <button class="btn-create" onclick="openCreateCourseModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>
                <span data-i18n="teacher_create_course">Create Course</span>
            </button>
        </div>

        <!-- Filters -->
        <div class="courses-filters">
            <div class="filter-group">
                <input type="text" id="searchTitle" placeholder="Search by title..." data-i18n="teacher_search_placeholder">
                <button onclick="applyFilters()" data-i18n="teacher_search">Search</button>
                <button onclick="clearFilters()" data-i18n="teacher_clear">Clear</button>
            </div>
            <div class="filter-group">
                <select id="statusFilter" onchange="applyFilters()">
                    <option value="" data-i18n="teacher_all_status">All Status</option>
                    <option value="published" data-i18n="teacher_status_published">Published</option>
                    <option value="draft" data-i18n="teacher_status_draft">Draft</option>
                    <option value="archived" data-i18n="teacher_status_archived">Archived</option>
                </select>
            </div>
        </div>

        <!-- Courses List -->
        <div id="coursesList">
            <div class="courses-grid" id="coursesGrid">
                <!-- Dynamically populated -->
            </div>
            <div id="noCourses" class="no-data" style="display:none;">
                <i class="fas fa-book-open"></i>
                <p data-i18n="teacher_no_courses">No courses found. Create your first course!</p>
            </div>
        </div>

    </div>
</div>

<!-- Create Course Modal -->
<div id="courseModal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h2 data-i18n="teacher_create_course">Create Course</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalError" class="modal-error" style="display:none;"></div>
            <form id="courseForm" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="title" data-i18n="teacher_course_title">Course Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Algebra Fundamentals" required>
                </div>

                <div class="form-group">
                    <label for="description" data-i18n="teacher_course_description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe your course content..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price" data-i18n="teacher_course_price">Price (DA)</label>
                        <input type="number" id="price" name="price" min="0" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label for="duration_weeks" data-i18n="teacher_course_duration">Duration (weeks)</label>
                        <input type="number" id="duration_weeks" name="duration_weeks" min="1" placeholder="8" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cover_image" data-i18n="teacher_course_cover">Cover Image</label>
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                    <small data-i18n="teacher_course_cover_note">JPG, PNG, WEBP (max 2MB)</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ccp_number" data-i18n="teacher_ccp_number">CCP Number (override)</label>
                        <input type="text" id="ccp_number" name="ccp_number" placeholder="e.g. 123456789">
                    </div>
                    <div class="form-group">
                        <label for="ccp_name" data-i18n="teacher_ccp_name">CCP Name (override)</label>
                        <input type="text" id="ccp_name" name="ccp_name" placeholder="e.g. Karim Benyahia">
                    </div>
                </div>

                <button type="submit" class="btn-submit" data-i18n="teacher_save_course">Save Course</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load courses
async function loadCourses() {
    const loadingState = document.getElementById('loadingState');
    const coursesContent = document.getElementById('coursesContent');

    loadingState.style.display = 'block';
    coursesContent.style.display = 'none';

    try {
        const title = document.getElementById('searchTitle')?.value || '';
        const status = document.getElementById('statusFilter')?.value || '';

        let url = '/api/Teacher/getTeacherCourses';
        const params = new URLSearchParams();
        if (title) params.append('title', title);
        if (status) params.append('status', status);
        if (params.toString()) url += '?' + params.toString();

        const response = await teacherFetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = response.data;
        renderCourses(data.courses || []);

        loadingState.style.display = 'none';
        coursesContent.style.display = 'block';

        const currentLang = document.documentElement.lang || 'en';
        if (typeof window.applyLanguage === 'function') {
            window.applyLanguage(currentLang);
        }

    } catch (error) {
        console.error('Error loading courses:', error);
        loadingState.style.display = 'none';
        showError('Failed to load courses.');
    }
}

// Render courses
function renderCourses(courses) {
    const grid = document.getElementById('coursesGrid');
    const noData = document.getElementById('noCourses');

    if (!courses || courses.length === 0) {
        grid.innerHTML = '';
        noData.style.display = 'block';
        return;
    }

    noData.style.display = 'none';

    const currentLang = document.documentElement.lang || 'en';
    const dict = window.translations?.[currentLang] || {};

    const statusLabels = {
        published: dict.teacher_status_published || 'Published',
        draft: dict.teacher_status_draft || 'Draft',
        archived: dict.teacher_status_archived || 'Archived'
    };

    grid.innerHTML = courses.map(course => `
        <div class="course-card">
            <div class="course-card-image">
                ${course.cover_image_url
                    ? `<img src="${course.cover_image_url}" alt="${course.title}">`
                    : `<div class="course-card-placeholder"><i class="fas fa-book"></i></div>`
                }
                <span class="course-status status-${course.status}">${statusLabels[course.status] || course.status}</span>
            </div>
            <div class="course-card-body">
                <h3>${course.title}</h3>
                <p class="course-description">${course.description ? course.description.substring(0, 100) + '...' : ''}</p>
                <div class="course-meta">
                    <span><i class="fas fa-users"></i> ${course.students_count || 0} ${dict.teacher_students || 'students'}</span>
                    <span><i class="fas fa-tag"></i> ${course.price} DA</span>
                    <span><i class="fas fa-calendar"></i> ${formatDate(course.created_at)}</span>
                </div>
                <div class="course-actions">
                    <button class="btn-edit" onclick="goToEditCourse(${course.id})">
                        <i class="fas fa-edit"></i> ${dict.teacher_edit || 'Edit'}
                    </button>
                    <button class="btn-view" onclick="viewCourse(${course.id})">
                        <i class="fas fa-eye"></i> ${dict.teacher_view || 'View'}
                    </button>
                    ${course.status === 'published' ? `
                        <button class="btn-manage" onclick="manageStudents(${course.id})">
                            <i class="fas fa-user-graduate"></i> ${dict.teacher_manage_students || 'Manage Students'}
                        </button>
                    ` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

// Open Create Course Modal
function openCreateCourseModal() {
    document.getElementById('courseForm').reset();
    document.getElementById('modalError').style.display = 'none';
    document.getElementById('courseModal').style.display = 'flex';
}

// Close Modal
function closeModal() {
    document.getElementById('courseModal').style.display = 'none';
}

// Navigate to the edit page
function goToEditCourse(courseId) {
    window.location.href = `/Teacher/Course/Edit/${courseId}`;
}

// Handle create course submission
document.getElementById('courseForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const errorBox = document.getElementById('modalError');
    errorBox.style.display = 'none';

    const submitBtn = this.querySelector('.btn-submit');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData(this);

    // Backend expects a nested "course" object, so we send bracketed
    // multipart fields (course[title], course[cover_image], etc.)
    // rather than JSON — JSON can't carry a File, and bracket notation
    // is what Laravel parses back into $request->course as an array.
    const payload = new FormData();
    payload.append('course[title]', formData.get('title'));
    payload.append('course[description]', formData.get('description'));
    payload.append('course[price]', formData.get('price'));
    payload.append('course[duration_weeks]', formData.get('duration_weeks'));
    payload.append('course[cover_image]', formData.get('cover_image'));

    const ccpNumber = formData.get('ccp_number');
    const ccpName = formData.get('ccp_name');
    if (ccpNumber) payload.append('course[ccp_number_override]', ccpNumber);
    if (ccpName) payload.append('course[ccp_name_override]', ccpName);

    try {
        const response = await teacherFetch('/api/Teacher/createCourse', {
            method: 'POST',
            data: payload,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.successCreateCourse) {
            alert(response.data.successCreateCourse);
            closeModal();
            loadCourses();
        }
    } catch (error) {
        errorBox.textContent = error.response?.data?.message || 'An error occurred. Please try again.';
        errorBox.style.display = 'block';
    }

    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
});

// Apply filters
function applyFilters() {
    loadCourses();
}

// Clear filters
function clearFilters() {
    document.getElementById('searchTitle').value = '';
    document.getElementById('statusFilter').value = '';
    loadCourses();
}

// View course
function viewCourse(courseId) {
    window.location.href = `/Teacher/Course/${courseId}`;
}

// Manage students
function manageStudents(courseId) {
    window.location.href = `/Teacher/Payments/${courseId}`;
}

// Helper functions
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
    errorDiv.className = 'courses-error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.querySelector('.teacher-courses').prepend(errorDiv);
}

// Load courses on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCourses();
});
</script>
@endpush
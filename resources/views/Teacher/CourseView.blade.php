@extends('layouts.teacher')

@section('content')
<div class="course-view-page">
    
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_course">Loading course...</p>
    </div>

    <div id="courseContent" style="display:none;">
        
        <div class="view-header">
            <a href="{{ route('teacher.courses') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> <span data-i18n="teacher_back_to_courses">Back to Courses</span>
            </a>
        </div>

        <div class="view-course-card">
            <div class="view-course-image" id="courseImageWrap">
                <div class="view-placeholder"><i class="fas fa-book fa-3x"></i></div>
            </div>
            <div class="view-course-info">
                <h1 id="courseTitle"></h1>
                <div class="view-meta">
                    <span class="course-status" id="courseStatus"></span>
                    <span><i class="fas fa-tag"></i> <span id="coursePrice"></span> DA</span>
                    <span><i class="fas fa-clock"></i> <span id="courseDuration"></span> <span data-i18n="teacher_weeks">weeks</span></span>
                </div>
                <p id="courseDescription" class="view-description"></p>
                <div class="view-actions">
                    <button class="btn-create" onclick="editFullCourse()">
                        <i class="fas fa-pencil-alt"></i> <span data-i18n="teacher_edit_full_course" >Edit Full Course</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="view-section">
            <h2 data-i18n="teacher_modules">Modules & Lessons</h2>
            <div id="modulesList" class="view-modules-list">
                <p class="no-modules" data-i18n="teacher_no_modules">No modules yet.</p>
            </div>
        </div>

    </div>
</div>

<!-- Lesson View Modal -->
<div id="lessonViewModal" class="modal-overlay" style="display:none;">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2 id="lessonViewTitle"></h2>
            <button class="modal-close" onclick="closeLessonViewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="lessonViewContent" class="lesson-view-content">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentCourseId = null;
let lessonData = {};

async function loadCourseView() {
    const courseId = getCourseIdFromUrl();
    currentCourseId = courseId;

    const loadingState = document.getElementById('loadingState');
    const courseContent = document.getElementById('courseContent');

    loadingState.style.display = 'block';
    courseContent.style.display = 'none';

    try {
        const response = await teacherFetch(`/api/Teacher/getCourseById?id=${courseId}`);
        const course = response.data.course;

        // Store lesson data for viewing
        if (course.modules) {
            course.modules.forEach(module => {
                if (module.lessons) {
                    module.lessons.forEach(lesson => {
                        lessonData[lesson.id] = lesson;
                    });
                }
            });
        }

        renderCourseView(course);

        loadingState.style.display = 'none';
        courseContent.style.display = 'block';

        const currentLang = document.documentElement.lang || 'en';
        if (typeof window.applyLanguage === 'function') {
            window.applyLanguage(currentLang);
        }

    } catch (error) {
        console.error('Error loading course:', error);
        loadingState.style.display = 'none';

        const notFoundMessage = error.response?.data?.CourseNotFound;
        showError(notFoundMessage || 'Failed to load course.');
    }
}

function getCourseIdFromUrl() {
    const parts = window.location.pathname.split('/');
    return parts[parts.length - 1];
}

function renderCourseView(course) {
    const currentLang = document.documentElement.lang || 'en';
    const dict = window.translations?.[currentLang] || {};

    // Image
    const imageWrap = document.getElementById('courseImageWrap');
    if (course.cover_image_url) {
        imageWrap.innerHTML = `<img src="${course.cover_image_url}" alt="${course.title}">`;
    } else {
        imageWrap.innerHTML = `<div class="view-placeholder"><i class="fas fa-book fa-3x"></i></div>`;
    }

    // Course info
    document.getElementById('courseTitle').textContent = course.title || 'Untitled Course';
    document.getElementById('coursePrice').textContent = course.price || 0;
    document.getElementById('courseDuration').textContent = course.duration_weeks || 0;
    document.getElementById('courseDescription').textContent = course.description || 'No description available.';

    // Status badge
    const statusLabels = {
        published: dict.teacher_status_published || 'Published',
        draft: dict.teacher_status_draft || 'Draft',
        archived: dict.teacher_status_archived || 'Archived'
    };
    const statusEl = document.getElementById('courseStatus');
    statusEl.textContent = statusLabels[course.status] || course.status || 'Draft';
    statusEl.className = `course-status status-${course.status || 'draft'}`;

    // Render modules (sorted defensively)
    const sortedModules = [...(course.modules || [])].sort(
        (a, b) => (a.order_index ?? 0) - (b.order_index ?? 0)
    );
    renderModules(sortedModules);
}

function renderModules(modules) {
    const container = document.getElementById('modulesList');

    if (!modules || modules.length === 0) {
        container.innerHTML = `<p class="no-modules" data-i18n="teacher_no_modules">No modules yet.</p>`;
        return;
    }

    container.innerHTML = modules.map((module, index) => {
        const sortedLessons = [...(module.lessons || [])].sort(
            (a, b) => (a.order_index ?? 0) - (b.order_index ?? 0)
        );

        return `
        <div class="view-module-card">
            <div class="view-module-header">
                <h3>${module.order_index || index + 1}. ${module.title || 'Untitled Module'}</h3>
                <span class="lesson-count">${sortedLessons.length} <span data-i18n="teacher_lessons">lessons</span></span>
            </div>
            <div class="view-lessons-list">
                ${sortedLessons.map((lesson, lIndex) => `
                    <div class="view-lesson-item clickable" onclick="openLessonView(${lesson.id})">
                        <span class="view-lesson-icon">
                            ${lesson.type === 'video' ? '<i class="fas fa-video"></i>' :
                              lesson.type === 'pdf' ? '<i class="fas fa-file-pdf"></i>' :
                              '<i class="fas fa-file-alt"></i>'}
                        </span>
                        <span class="view-lesson-title">${lesson.order_index || lIndex + 1}. ${lesson.title || 'Untitled Lesson'}</span>
                        <span class="view-lesson-type">${lesson.type || 'text'}</span>
                        <span class="view-lesson-play">
                            <i class="fas fa-play-circle"></i>
                        </span>
                    </div>
                `).join('')}
            </div>
        </div>
        `;
    }).join('');
}

function openLessonView(lessonId) {
    const lesson = lessonData[lessonId];
    if (!lesson) {
        alert('Lesson data not found.');
        return;
    }

    const modal = document.getElementById('lessonViewModal');
    const title = document.getElementById('lessonViewTitle');
    const content = document.getElementById('lessonViewContent');

    title.textContent = lesson.title || 'Untitled Lesson';

    // Render content based on type
    let html = '';
    const type = lesson.type || 'text';

    if (type === 'text') {
        html = `
            <div class="lesson-text-content">
                ${lesson.content_url || 'No content available.'}
            </div>
        `;
    } else if (type === 'video') {
        const videoUrl = lesson.content_url || '';
        html = `
            <div class="lesson-video-content">
                ${videoUrl ? `
                    <video controls class="lesson-video-player">
                        <source src="${videoUrl}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                ` : '<p>No video available.</p>'}
            </div>
        `;
    } else if (type === 'pdf') {
        const pdfUrl = lesson.content_url || '';
        html = `
            <div class="lesson-pdf-content">
                ${pdfUrl ? `
                    <div class="pdf-viewer">
                        <iframe src="${pdfUrl}" class="pdf-iframe"></iframe>
                    </div>
                    <div class="pdf-download">
                        <a href="${pdfUrl}" target="_blank" class="btn-download-pdf">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                ` : '<p>No PDF available.</p>'}
            </div>
        `;
    }

    content.innerHTML = html;
    modal.style.display = 'flex';
}

function closeLessonViewModal() {
    const modal = document.getElementById('lessonViewModal');
    const video = modal.querySelector('video');
    if (video) {
        video.pause();
    }
    modal.style.display = 'none';
}

function editFullCourse() {
    if (currentCourseId) {
        window.location.href = `/Teacher/Course/Edit/${currentCourseId}`;
    }
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'view-error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    document.querySelector('.course-view-page').prepend(errorDiv);
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('lessonViewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLessonViewModal();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    loadCourseView();
});
</script>
@endpush
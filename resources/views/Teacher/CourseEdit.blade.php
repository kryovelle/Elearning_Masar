@extends('layouts.teacher')

@section('content')
<div class="course-edit-page">
    
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_course">Loading course...</p>
    </div>

    <div id="editContent" style="display:none;">
        
        <div class="edit-header">
            <div class="edit-header-left">
                <a href="{{ route('teacher.courses') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> <span data-i18n="teacher_back_to_courses">Back to Courses</span>
                </a>
                <h1 data-i18n="teacher_edit_course_title">Edit Course</h1>
            </div>
            <button class="btn-save-all" onclick="saveAllChanges()">
                <i class="fas fa-save"></i> <span data-i18n="teacher_save_all">Save All Changes</span>
            </button>
        </div>

        <div id="editError" class="edit-error" style="display:none;"></div>
        <div id="editSuccess" class="edit-success" style="display:none;"></div>

        <!-- Basic Info Section -->
        <div class="edit-section">
            <h2 data-i18n="teacher_basic_info">Basic Information</h2>
            <div class="edit-grid">
                <div class="form-group">
                    <label for="title" data-i18n="teacher_course_title">Course Title</label>
                    <input type="text" id="title" class="form-control">
                </div>
                <div class="form-group">
                    <label for="price" data-i18n="teacher_course_price">Price (DA)</label>
                    <input type="number" id="price" class="form-control">
                </div>
                <div class="form-group">
                    <label for="duration_weeks" data-i18n="teacher_course_duration">Duration (weeks)</label>
                    <input type="number" id="duration_weeks" class="form-control">
                </div>
                <div class="form-group">
                    <label for="status" data-i18n="teacher_status">Status</label>
                    <select id="status" class="form-control">
                        <option value="draft" data-i18n="teacher_status_draft">Draft</option>
                        <option value="published" data-i18n="teacher_status_published">Published</option>
                        <option value="archived" data-i18n="teacher_status_archived">Archived</option>
                    </select>
                </div>
                <div class="form-group form-group-checkbox">
                    <label for="featured">
                        <input type="checkbox" id="featured">
                        <span data-i18n="teacher_featured">Featured Course</span>
                    </label>
                    <small data-i18n="teacher_featured_help">Featured courses are highlighted to students.</small>
                </div>
                <div class="form-group full-width">
                    <label for="description" data-i18n="teacher_course_description">Description</label>
                    <textarea id="description" rows="4" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="ccp_number" data-i18n="teacher_ccp_number">CCP Number (override)</label>
                    <input type="text" id="ccp_number" class="form-control">
                </div>
                <div class="form-group">
                    <label for="ccp_name" data-i18n="teacher_ccp_name">CCP Name (override)</label>
                    <input type="text" id="ccp_name" class="form-control">
                </div>
            </div>
        </div>

        <!-- Modules & Lessons Section -->
        <div class="edit-section">
            <div class="section-header">
                <h2 data-i18n="teacher_modules_lessons">Modules & Lessons</h2>
                <button class="btn-add-module" onclick="addModule()">
                    <i class="fas fa-plus"></i> <span data-i18n="teacher_add_module">Add Module</span>
                </button>
            </div>
            <div id="modulesContainer" class="modules-container"></div>
        </div>

    </div>
</div>

<!-- Lesson Modal -->
<div id="lessonModal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h2 id="lessonModalTitle" data-i18n="teacher_add_lesson">Add Lesson</h2>
            <button class="modal-close" onclick="closeLessonModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="lessonError" class="modal-error" style="display:none;"></div>
            <form id="lessonForm">
                <input type="hidden" id="lessonModuleId">
                <input type="hidden" id="lessonEditId">
                
                <div class="form-group">
                    <label for="lessonTitle" data-i18n="teacher_lesson_title">Lesson Title</label>
                    <input type="text" id="lessonTitle" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="lessonType" data-i18n="teacher_lesson_type">Lesson Type</label>
                    <select id="lessonType" class="form-control" onchange="toggleLessonContent()">
                        <option value="text" data-i18n="teacher_lesson_text">Text</option>
                        <option value="video" data-i18n="teacher_lesson_video">Video</option>
                        <option value="pdf" data-i18n="teacher_lesson_pdf">PDF</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="lessonContent" data-i18n="teacher_lesson_content">Content</label>
                    <div id="textContentArea">
                        <textarea id="lessonContentText" rows="6" class="form-control" placeholder="Enter lesson content..."></textarea>
                    </div>
                    <div id="fileContentArea" style="display:none;">
                        <input type="file" id="lessonContentFile" class="form-control" accept=".mp4,.mov,.avi,.mkv,.pdf">
                        <small data-i18n="teacher_lesson_file_help">Upload video (MP4) or PDF file</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" data-i18n="teacher_save_lesson">Save Lesson</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let courseId = null;
let tempCounter = 0;
let deletedModules = [];
let deletedLessons = [];

// Source of truth — NOT the DOM. Each module/lesson keeps a stable key
// (real DB id, or a temp string for new/unsaved items) plus real content.
let modulesState = [];

async function loadCourseEdit() {
    courseId = getCourseIdFromUrl();
    const loadingState = document.getElementById('loadingState');
    const editContent = document.getElementById('editContent');

    loadingState.style.display = 'block';
    editContent.style.display = 'none';

    try {
        const response = await teacherFetch(`/api/Teacher/getCourseById?id=${courseId}`);
        const course = response.data.course;

        document.getElementById('title').value = course.title || '';
        document.getElementById('price').value = course.price || '';
        document.getElementById('featured').checked = !!course.featured;
        document.getElementById('duration_weeks').value = course.duration_weeks || '';
        document.getElementById('status').value = course.status || 'draft';
        document.getElementById('description').value = course.description || '';
        document.getElementById('ccp_number').value = course.ccp_number_override || '';
        document.getElementById('ccp_name').value = course.ccp_name_override || '';

        modulesState = (course.modules || [])
            .sort((a, b) => (a.order_index ?? 0) - (b.order_index ?? 0))
            .map(m => ({
                key: m.id,             // real DB id — always a number for existing modules
                id: m.id,
                title: m.title || '',
                lessons: (m.lessons || [])
                    .sort((a, b) => (a.order_index ?? 0) - (b.order_index ?? 0))
                    .map(l => ({
                        key: l.id,      // real DB id
                        id: l.id,
                        title: l.title || '',
                        type: l.type || 'text',
                        existingContentUrl: l.content_url || null,
                        // text content isn't returned separately by the API today —
                        // if type is text, content_url currently holds the raw text
                        // (see earlier note about giving text lessons their own column)
                        content: null,       // pending change: string (text) or File (video/pdf)
                        contentChanged: false,
                    })),
            }));

        renderModules();

        loadingState.style.display = 'none';
        editContent.style.display = 'block';

        const currentLang = document.documentElement.lang || 'en';
        if (typeof window.applyLanguage === 'function') {
            window.applyLanguage(currentLang);
        }

    } catch (error) {
        console.error('Error loading course:', error);
        loadingState.style.display = 'none';
        showError(error.response?.data?.CourseNotFound || 'Failed to load course data.');
    }
}

function getCourseIdFromUrl() {
    const parts = window.location.pathname.split('/');
    return parts[parts.length - 1];
}

function newTempKey() {
    return 'new_' + (tempCounter++);
}

function findModule(moduleKey) {
    return modulesState.find(m => String(m.key) === String(moduleKey));
}

function findLesson(moduleKey, lessonKey) {
    const m = findModule(moduleKey);
    return m ? m.lessons.find(l => String(l.key) === String(lessonKey)) : null;
}

function renderModules() {
    const container = document.getElementById('modulesContainer');

    if (modulesState.length === 0) {
        container.innerHTML = `
            <div class="empty-modules">
                <i class="fas fa-layer-group"></i>
                <p data-i18n="teacher_no_modules_yet">No modules yet. Click "Add Module" to get started.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = modulesState.map((module, index) => `
        <div class="module-card" data-module-key="${module.key}">
            <div class="module-card-header">
                <div class="module-title-group">
                    <span class="module-number">${index + 1}</span>
                    <input type="text" class="module-title-input" value="${module.title}"
                        placeholder="Module title..." onchange="updateModuleTitle('${module.key}', this.value)">
                </div>
                <div class="module-actions">
                    <button class="btn-add-lesson" onclick="openLessonModal('${module.key}')">
                        <i class="fas fa-plus"></i> <span data-i18n="teacher_add_lesson">Add Lesson</span>
                    </button>
                    <button class="btn-delete-module" onclick="deleteModule('${module.key}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="lessons-container">
                ${module.lessons.map(lesson => `
                    <div class="lesson-item" data-lesson-key="${lesson.key}">
                        <span class="lesson-icon">
                            ${lesson.type === 'video' ? '<i class="fas fa-video"></i>' :
                              lesson.type === 'pdf' ? '<i class="fas fa-file-pdf"></i>' :
                              '<i class="fas fa-file-alt"></i>'}
                        </span>
                        <span class="lesson-title">${lesson.title}</span>
                        <span class="lesson-type">${lesson.type}</span>
                        <div class="lesson-actions">
                            <button class="btn-edit-lesson" onclick="openLessonModal('${module.key}', '${lesson.key}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-delete-lesson" onclick="deleteLesson('${module.key}', '${lesson.key}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `).join('');
}

function updateModuleTitle(moduleKey, value) {
    const m = findModule(moduleKey);
    if (m) m.title = value;
}

function addModule() {
    modulesState.push({ key: newTempKey(), id: null, title: '', lessons: [] });
    renderModules();
}

function deleteModule(moduleKey) {
    if (!confirm('Delete this module and all its lessons?')) return;
    const m = findModule(moduleKey);
    if (!m) return;
    if (m.id) deletedModules.push(m.id); // only real DB rows need server-side deletion
    modulesState = modulesState.filter(mod => mod.key !== m.key);
    renderModules();
}

let activeLessonModule = null;
let activeLessonKey = null;

function openLessonModal(moduleKey, lessonKey = null) {
    activeLessonModule = moduleKey;
    activeLessonKey = lessonKey;

    document.getElementById('lessonError').style.display = 'none';
    document.getElementById('lessonContentText').value = '';
    document.getElementById('lessonContentFile').value = '';

    const lesson = lessonKey ? findLesson(moduleKey, lessonKey) : null;

    document.getElementById('lessonTitle').value = lesson?.title || '';
    document.getElementById('lessonType').value = lesson?.type || 'text';

    if (lesson?.type === 'text' && lesson.existingContentUrl) {
        // see note above re: text content currently living in content_url
        document.getElementById('lessonContentText').value = lesson.existingContentUrl;
    }

    document.getElementById('lessonModalTitle').textContent = lessonKey ? 'Edit Lesson' : 'Add Lesson';
    toggleLessonContent();
    document.getElementById('lessonModal').style.display = 'flex';
}

function closeLessonModal() {
    document.getElementById('lessonModal').style.display = 'none';
    activeLessonModule = null;
    activeLessonKey = null;
}

function toggleLessonContent() {
    const type = document.getElementById('lessonType').value;
    document.getElementById('textContentArea').style.display = type === 'text' ? 'block' : 'none';
    document.getElementById('fileContentArea').style.display = type !== 'text' ? 'block' : 'none';
}

document.getElementById('lessonForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const title = document.getElementById('lessonTitle').value.trim();
    const type = document.getElementById('lessonType').value;

    if (!title) {
        document.getElementById('lessonError').textContent = 'Lesson title is required.';
        document.getElementById('lessonError').style.display = 'block';
        return;
    }

    // Only text/video/pdf are valid lesson types
    let content = null;
    let contentChanged = false;

    if (type === 'text') {
        const text = document.getElementById('lessonContentText').value;
        content = text;
        contentChanged = true; // text is always re-sent as-is; cheap and simple
    } else {
        const file = document.getElementById('lessonContentFile').files[0];
        if (file) {
            content = file;
            contentChanged = true;
        }
        // no new file picked on an edit -> leave content untouched (contentChanged stays false)
    }

    const module = findModule(activeLessonModule);
    if (!module) return;

    if (activeLessonKey) {
        const lesson = findLesson(activeLessonModule, activeLessonKey);
        if (lesson) {
            lesson.title = title;
            lesson.type = type;
            if (contentChanged) {
                lesson.content = content;
                lesson.contentChanged = true;
            }
        }
    } else {
        module.lessons.push({
            key: newTempKey(),
            id: null,
            title,
            type,
            existingContentUrl: null,
            content,
            contentChanged,
        });
    }

    renderModules();
    closeLessonModal();
});

function deleteLesson(moduleKey, lessonKey) {
    if (!confirm('Delete this lesson?')) return;
    const module = findModule(moduleKey);
    if (!module) return;
    const lesson = findLesson(moduleKey, lessonKey);
    if (lesson?.id) deletedLessons.push(lesson.id);
    module.lessons = module.lessons.filter(l => l.key !== lessonKey);
    renderModules();
}

async function saveAllChanges() {
    const errorBox = document.getElementById('editError');
    const successBox = document.getElementById('editSuccess');
    errorBox.style.display = 'none';
    successBox.style.display = 'none';

    const saveBtn = document.querySelector('.btn-save-all');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        const payload = buildFormData();

        const response = await teacherFetch('/api/Teacher/editCourse', {
            method: 'POST',
            data: payload,
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data.successEditCourse) {
            successBox.textContent = response.data.successEditCourse;
            successBox.style.display = 'block';
            deletedModules = [];
            deletedLessons = [];
            setTimeout(() => location.reload(), 2000);
        }
    } catch (error) {
        errorBox.textContent = error.response?.data?.message || 'An error occurred.';
        errorBox.style.display = 'block';
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

function buildFormData() {
    const fd = new FormData();

    fd.append('course[id]', courseId);
     fd.append('course[featured]', document.getElementById('featured').checked ? '1' : '0');
    fd.append('course[title]', document.getElementById('title').value);
    fd.append('course[description]', document.getElementById('description').value);
    fd.append('course[price]', document.getElementById('price').value);
    fd.append('course[duration_weeks]', document.getElementById('duration_weeks').value);
    fd.append('course[status]', document.getElementById('status').value);
    fd.append('course[ccp_number_override]', document.getElementById('ccp_number').value || '');
    fd.append('course[ccp_name_override]', document.getElementById('ccp_name').value || '');

    modulesState.forEach((module, mi) => {
        if (module.id) fd.append(`modules[${mi}][id]`, module.id);
        fd.append(`modules[${mi}][title]`, module.title);
        fd.append(`modules[${mi}][order_index]`, mi + 1);

        module.lessons.forEach((lesson, li) => {
            if (lesson.id) fd.append(`modules[${mi}][lessons][${li}][id]`, lesson.id);
            fd.append(`modules[${mi}][lessons][${li}][title]`, lesson.title);
            fd.append(`modules[${mi}][lessons][${li}][type]`, lesson.type);
            fd.append(`modules[${mi}][lessons][${li}][order_index]`, li + 1);
            if (lesson.contentChanged && lesson.content !== null) {
                fd.append(`modules[${mi}][lessons][${li}][content]`, lesson.content);
            }
        });
    });

    deletedModules.forEach(id => fd.append('deleted_modules[]', id));
    deletedLessons.forEach(id => fd.append('deleted_lessons[]', id));

    return fd;
}

function showError(message) {
    const errorBox = document.getElementById('editError');
    errorBox.textContent = message;
    errorBox.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function () {
    loadCourseEdit();
});
</script>
@endpush
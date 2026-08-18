@extends('layouts.student')

@section('content')
<div class="player-page">

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="student_loading_course">Loading course...</p>
    </div>

    <div id="playerError" class="empty-state" style="display:none;">
        <i class="fas fa-triangle-exclamation"></i>
        <h3 id="playerErrorText"></h3>
        <a href="/Student/Courses" class="btn-primary" data-i18n="student_back_to_courses">Back to Courses</a>
    </div>

    <div id="playerContent" style="display:none;">

        <div class="player-topbar">
            <a href="/Student/Courses" class="back-link">
                <i class="fas fa-arrow-left"></i> <span data-i18n="student_back_to_courses">Back to Courses</span>
            </a>
            <h2 id="playerCourseTitle"></h2>
        </div>

        <div class="player-body">

            <aside class="player-sidebar" id="playerSidebar"></aside>

            <div class="player-main">

                <div class="lesson-heading">
                    <span id="lessonBreadcrumb"></span>
                </div>

                <div class="lesson-media" id="lessonMedia"></div>

                <h3 id="lessonTitle"></h3>

                <div class="lesson-nav">
                    <button id="prevLessonBtn" class="btn-lesson-nav" disabled>
                        <i class="fas fa-arrow-left"></i> <span data-i18n="student_previous">Previous</span>
                    </button>
                    <button id="nextLessonBtn" class="btn-lesson-nav">
                        <span data-i18n="student_next">Next</span> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <div class="player-section">
                    <h4 data-i18n="student_resources">Resources</h4>
                    <div id="resourceList" class="resource-list"></div>
                </div>
                <!---
                <div class="player-section">
                    <h4 data-i18n="student_ask_question">Ask a Question</h4>
                    <div id="askError" class="field-error" style="display:none;"></div>
                    <div class="ask-box">
                        <input type="text" id="questionInput" placeholder="">
                        <button id="sendQuestionBtn" class="btn-send" data-i18n="student_send">Send</button>
                    </div>
                    <p id="askConfirmation" class="ask-confirmation" style="display:none;" data-i18n="student_question_sent">Your question was sent to the teacher.</p>
                </div>
            
            !--->

            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
let courseData = null;
let flatLessons = []; // [{ moduleId, moduleTitle, lesson }]
let currentLessonIndex = 0;

function getCourseIdFromPath() {
    const parts = window.location.pathname.split('/').filter(Boolean);
    return parts[parts.length - 1];
}

async function fetchCoursePlayer() {
    const id = getCourseIdFromPath();

    try {
        const res = await studentFetch(`/api/Student/getCourseById?id=${id}`);
        if (!res) return;

        const data = res.data;

        document.getElementById('loadingState').style.display = 'none';

        courseData = data;
        buildFlatLessons(data.modules || []);
        renderPlayerShell(data.course);
        renderSidebar(data.modules || []);

        if (flatLessons.length) {
            selectLesson(0);
        }

        document.getElementById('playerContent').style.display = 'block';

    } catch (e) {
        const error=e?.response?.data?.error ||e?.response?.data?.message || 'Error loading course';
        console.error(error);
        document.getElementById('loadingState').style.display = 'none';
        showPlayerError(error);
    }
}

function showPlayerError(message) {
    document.getElementById('playerErrorText').textContent = message;
    document.getElementById('playerError').style.display = 'block';
}

function buildFlatLessons(modules) {
    flatLessons = [];
    modules.forEach(m => {
        (m.lessons || []).forEach(l => {
            flatLessons.push({ moduleId: m.id, moduleTitle: m.title, lesson: l });
        });
    });
}

function renderPlayerShell(course) {
    document.getElementById('playerCourseTitle').textContent = course.title || '';
}

function lessonIcon(type) {
    if (type === 'video') return 'fa-circle-play';
    if (type === 'pdf') return 'fa-file-pdf';
    return 'fa-file-lines';
}

function renderSidebar(modules) {
    const sidebar = document.getElementById('playerSidebar');

    sidebar.innerHTML = modules.map(m => `
        <div class="sidebar-module">
            <div class="sidebar-module-title">${m.title || ''}</div>
            <div class="sidebar-lessons">
                ${(m.lessons || []).map(l => {
                    const flatIndex = flatLessons.findIndex(f => f.lesson.id === l.id);
                    return `
                        <button class="sidebar-lesson" data-index="${flatIndex}">
                            <i class="fas ${lessonIcon(l.type)}"></i>
                            <span>${l.title || ''}</span>
                        </button>
                    `;
                }).join('')}
            </div>
        </div>
    `).join('');

    sidebar.querySelectorAll('.sidebar-lesson').forEach(btn => {
        btn.addEventListener('click', () => selectLesson(parseInt(btn.dataset.index, 10)));
    });
}

function selectLesson(index) {
    if (index < 0 || index >= flatLessons.length) return;

    currentLessonIndex = index;
    const { moduleTitle, lesson } = flatLessons[index];

    document.querySelectorAll('.sidebar-lesson').forEach(btn => {
        btn.classList.toggle('active', parseInt(btn.dataset.index, 10) === index);
    });

    document.getElementById('lessonBreadcrumb').textContent = `${moduleTitle}`;
    document.getElementById('lessonTitle').textContent = lesson.title || '';

    renderLessonMedia(lesson);
    renderResources(lesson);

    document.getElementById('prevLessonBtn').disabled = index === 0;
    document.getElementById('nextLessonBtn').disabled = index === flatLessons.length - 1;
}

function renderLessonMedia(lesson) {
    const media = document.getElementById('lessonMedia');

    if (lesson.type === 'video') {
        media.innerHTML = `<video controls style="width:100%;height:100%;border-radius:16px;" src="${lesson.content_url}"></video>`;
    } else if (lesson.type === 'pdf') {
        media.innerHTML = `<iframe src="${lesson.content_url}" style="width:100%;height:100%;border:none;border-radius:16px;"></iframe>`;
    } else {
        media.innerHTML = `<div class="lesson-text-content">${lesson.content_url || ''}</div>`;
    }
}

function renderResources(lesson) {
    const list = document.getElementById('resourceList');
    const t = window.translations?.[document.documentElement.lang] || {};

    if (lesson.type === 'pdf') {
        list.innerHTML = `
            <div class="resource-item">
                <span><i class="fas fa-file-pdf"></i> ${lesson.title || 'Lesson'}.pdf</span>
                <a href="${lesson.content_url}" download class="btn-download">${t.student_download || 'Download'}</a>
            </div>
        `;
    } else {
        list.innerHTML = `<p class="no-resources">${t.student_no_resources || 'No downloadable resources for this lesson.'}</p>`;
    }
}

document.getElementById('prevLessonBtn').addEventListener('click', () => selectLesson(currentLessonIndex - 1));
document.getElementById('nextLessonBtn').addEventListener('click', () => selectLesson(currentLessonIndex + 1));



document.addEventListener('DOMContentLoaded', fetchCoursePlayer);
</script>
@endpush
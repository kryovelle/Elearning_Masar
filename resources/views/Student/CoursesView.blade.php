@extends('layouts.student')

@section('content')
<div class="my-courses-page">

   <div class="page-heading" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h1 data-i18n="student_my_courses">My Courses</h1>
        <p data-i18n="student_my_courses_sub">Courses you're currently enrolled and approved in.</p>
    </div>

    <a href="/Public/Courses" class="btn-primary" data-i18n="student_browse_courses"
       target="_blank" rel="noopener noreferrer"
       style="height:50px; display:flex; align-items:center; padding:0 24px; white-space:nowrap;">
       Browse
    </a>
</div>

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="student_loading_courses">Loading your courses...</p>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fas fa-graduation-cap"></i>
        <h3 data-i18n="student_no_approved_courses">No approved courses yet</h3>
        <p data-i18n="student_no_approved_courses_sub">Once your enrollment is approved, your courses will show up here.</p>
        <a href="/Public/Courses" class="btn-primary" data-i18n="student_browse_courses">Browse courses</a>
    </div>

    <div id="myCoursesGrid" class="my-courses-grid"></div>

</div>
@endsection


@push('scripts')
<script>
function handleCourseImgError(img) {
    const parent = img.parentElement;
    parent.innerHTML = `<i class="fas fa-square-root-variable fa-2x"></i>`;
}

async function fetchMyCourses() {
    try {
        const res = await studentFetch('/api/Student/getApprovedCourses');
        if (!res) return;

        const data = res.data;

        document.getElementById('loadingState').style.display = 'none';
        renderMyCourses(data.approved_courses || []);
    } catch (error) {
        console.error('Error fetching approved courses:', error);
        document.getElementById('loadingState').style.display = 'none';
    }
}

function renderMyCourses(courses) {
    const grid = document.getElementById('myCoursesGrid');
    const empty = document.getElementById('emptyState');

    if (!courses.length) {
        grid.innerHTML = '';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';

    const t = window.translations?.[document.documentElement.lang] || {};
    const openText = t.student_open_course || 'Open';
    const weeksText = t.course_detail_weeks || 'weeks';

    grid.innerHTML = courses.map(c => `
        <div class="my-course-card">
            <div class="my-course-img">
                ${c.cover_image_url
                    ? `<img src="${c.cover_image_url}" style="width:100%;height:100%;object-fit:cover;border-radius:20px;" onerror="handleCourseImgError(this)">`
                    : `<i class="fas fa-square-root-variable fa-2x"></i>`}
            </div>
            <h4>${c.title || ''}</h4>
            <p class="my-course-desc">${c.description || ''}</p>
            <div class="my-course-meta">${c.duration_weeks || ''} ${weeksText}</div>
            <a href="/Student/CoursePlayer/${c.id}" class="btn-open-course">${openText} <i class="fas fa-arrow-right"></i></a>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', fetchMyCourses);
window.handleCourseImgError = handleCourseImgError;
</script>
@endpush
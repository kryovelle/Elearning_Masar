{{-- resources/views/courses.blade.php --}}
@extends('layouts.public')

@section('content')
<div class="courses-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <span class="eyebrow" data-i18n="courses_eyebrow">Featured courses</span>
            <h1 data-i18n="courses_h2" style="color:#3454d1">Pick up where you're stuck</h1>
            <p data-i18n="courses_p">Every course is built around one exam cycle — full recordings, practice sheets, and direct access to ask the teacher questions.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <div class="filters-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    id="courseSearch" 
                    placeholder="{{ __('Search courses...') }}"
                    data-i18n-placeholder="search_placeholder"
                    class="search-input"
                >
            </div>
            
            <div class="filter-group">
                <select id="priceFilter" class="filter-select">
                    <option value="" data-i18n="all_prices">All prices</option>
                    <option value="1000" data-i18n="under_1000">Under 1000 DA</option>
                    <option value="2000" data-i18n="under_2000">Under 2000 DA</option>
                    <option value="3000" data-i18n="under_3000">Under 3000 DA</option>
                    <option value="5000" data-i18n="under_5000">Under 5000 DA</option>
                </select>
            </div>

            <button id="clearFilters" class="btn-clear" data-i18n="clear_filters">
                <i class="fas fa-times"></i> Clear filters
            </button>
        </div>
    </div>

    <!-- Results Count -->
    <div class="results-info">
        <span id="resultsCount" data-i18n="showing_results">Showing <span id="countNumber">0</span> courses</span>
    </div>

    <!-- Course Grid -->
    <div id="courseGrid" class="course-grid">
        <!-- Courses will be rendered here by JavaScript -->
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state" style="display: none;">
        <div class="spinner"></div>
        <p data-i18n="loading_courses">Loading courses...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state" style="display: none;">
        <i class="fas fa-book-open"></i>
        <h3 data-i18n="no_courses_found">No courses found</h3>
        <p data-i18n="try_adjusting_filters">Try adjusting your filters or search terms.</p>
        <button onclick="resetFilters()" class="btn-primary" data-i18n="reset_filters">Reset filters</button>
    </div>
</div>
@endsection


@push('scripts')

<script>

  
// Handle course image fallback
function handleCourseImgError(img) {
    const fallbackIcon = img.getAttribute('data-fallback-icon') || 'fa-square-root-variable';
    const parent = img.parentElement;
    parent.innerHTML = `<i class="fas ${fallbackIcon} fa-2x"></i>`;
}

// Fetch courses with filters
async function fetchCourses() {
    const search = document.getElementById('courseSearch').value.trim();
    const price = document.getElementById('priceFilter').value;

    let url = '/api/Public/getPublishedCourses?';
    const params = [];
    
    if (search) params.push(`title=${encodeURIComponent(search)}`);
    if (price) params.push(`price=${price}`);
    
    url += params.join('&');

    showLoading(true);

    try {
        const response = await fetch(url);
        const data_ar = await response.json();
        const courses=data_ar.courses;


        showLoading(false);
        renderCourses(courses);
        updateResultsCount(courses.length);
    } catch (error) {
        console.error('Error fetching courses:', error);
        showLoading(false);
        showEmptyState();
    }
}

// Render courses
function renderCourses(courses) {
    const grid = document.getElementById('courseGrid');
    const emptyState = document.getElementById('emptyState');

    if (!courses || courses.length === 0) {
        grid.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';

    const translations = window.translations?.[document.documentElement.lang] || {};
    const detailsText = translations?.details || 'Details';
    const priceUnit = translations?.price_unit || '/ course';

    grid.innerHTML = courses.map(c => `
        <div class="course-card">
            <div class="course-img">
                ${c.cover_image_url
                    ? `<img src="${c.cover_image_url}" data-fallback-icon="${c.icon || 'fa-square-root-variable'}" style="width:100%;height:100%;object-fit:cover;border-radius:24px;" onerror="handleCourseImgError(this)">`
                    : `<i class="fas ${c.icon || 'fa-square-root-variable'} fa-2x"></i>`}
            </div>
            <h4>${c.title || 'Course Name'}</h4>
            <div class="meta">${c.weeks_duration || '8 weeks'}</div>
            <div class="price">${c.price || '3000'} DA <span style="font-size:0.7rem;font-weight:400;color:#6d7395;">${priceUnit}</span></div>
            <span class="btn-details" onclick="window.location.href='/Public/CourseDetail/${c.id}'">
                <span>${detailsText}</span> <i class="fas fa-arrow-right"></i>
            </span>
        </div>
    `).join('');
}

// Update results count
function updateResultsCount(count) {
    const countEl = document.getElementById('countNumber');
    if (countEl) countEl.textContent = count;
}

// Show/hide loading
function showLoading(show) {
    const loading = document.getElementById('loadingState');
    const grid = document.getElementById('courseGrid');
    const empty = document.getElementById('emptyState');

    if (show) {
        loading.style.display = 'block';
        grid.style.display = 'none';
        empty.style.display = 'none';
    } else {
        loading.style.display = 'none';
        grid.style.display = 'grid';
    }
}

// Show empty state
function showEmptyState() {
    document.getElementById('courseGrid').innerHTML = '';
    document.getElementById('emptyState').style.display = 'block';
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('courseGrid').style.display = 'grid';
    updateResultsCount(0);
}

// Reset filters
function resetFilters() {
    document.getElementById('courseSearch').value = '';
    document.getElementById('priceFilter').value = '';
    fetchCourses();
}

// Debounce helper
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Initial load
    fetchCourses();

    // Search with debounce
    const searchInput = document.getElementById('courseSearch');
    searchInput.addEventListener('input', debounce(fetchCourses, 300));

    // Price filter
    document.getElementById('priceFilter').addEventListener('change', fetchCourses);

    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', resetFilters);
});

// Handle RTL for search placeholder
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('courseSearch');
    const lang = document.documentElement.lang || 'en';
    
    const placeholders = {
        en: 'Search courses...',
        fr: 'Rechercher des cours...',
        ar: 'ابحث عن دورات...'
    };
    
    searchInput.placeholder = placeholders[lang] || placeholders.en;
});

// Make functions global for inline usage
window.handleCourseImgError = handleCourseImgError;
window.resetFilters = resetFilters;
window.fetchCourses = fetchCourses;
</script>
@endpush
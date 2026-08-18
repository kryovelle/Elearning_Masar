@extends('layouts.public')

@section('content')
<div class="about-page">

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p data-i18n="loading_teacher">Loading profile...</p>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fas fa-user-slash"></i>
        <h3 data-i18n="teacher_not_found">Profile not found</h3>
    </div>

    <div id="aboutContent" style="display:none;">

        <div class="about-hero">
            <div class="about-photo" id="teacherPhotoWrap"></div>

            <div class="about-heading">
                <h1 id="teacherName"></h1>
                <span class="about-badge" id="teacherRole"></span>
            </div>
        </div>

        <div class="about-section">
            <h2 data-i18n="about_bio_title">Bio</h2>
            <p id="teacherBio"></p>
        </div>

        <div class="about-section">
            <h2 data-i18n="about_contact_title">Contact</h2>
            <div class="contact-grid" id="contactGrid"></div>
        </div>

    </div>
</div>
@endsection


@push('scripts')
<script>
// Photo fallback (same style as course image fallback)
function handleTeacherImgError(img) {
    const parent = img.parentElement;
    parent.innerHTML = `<i class="fas fa-user fa-3x"></i>`;
}

// Fetch both teacher profile and social links, then render together
async function fetchAboutData() {
    showLoading(true);

    try {
        const [teacherRes, homeRes] = await Promise.all([
            fetch('/api/Public/getTeacherData'),
            fetch('/api/Public/getHomePageData')
        ]);

        const teacherData = await teacherRes.json();
        const homeData = await homeRes.json();

        const teacher = teacherData.teacher?.[0];
        const socialLinks = homeData?.[0]?.social_links || {};

        if (!teacher) {
            showLoading(false);
            showEmptyState();
            return;
        }

        showLoading(false);
        renderTeacher(teacher, socialLinks);
    } catch (error) {
        console.error('Error fetching about page data:', error);
        showLoading(false);
        showEmptyState();
    }
}

// Render teacher profile + contact pills
function renderTeacher(t, social) {
    document.getElementById('aboutContent').style.display = 'block';

    const photoWrap = document.getElementById('teacherPhotoWrap');
    photoWrap.innerHTML = t.photo_url
        ? `<img src="${t.photo_url}" style="width:100%;height:100%;object-fit:cover;border-radius:32px;" onerror="handleTeacherImgError(this)">`
        : `<i class="fas fa-user fa-3x"></i>`;

    document.getElementById('teacherName').textContent = t.name || '';

    const translations = window.translations?.[document.documentElement.lang] || {};
    document.getElementById('teacherRole').textContent = translations?.math_physics_teacher || 'Math & Physics teacher';

    document.getElementById('teacherBio').textContent = t.bio || '';

    const contacts = [];

    if (t.email) {
        contacts.push(`<a href="mailto:${t.email}" class="contact-pill"><i class="fas fa-envelope"></i> <span>${t.email}</span></a>`);
    }
    if (t.phone) {
        contacts.push(`<a href="tel:${t.phone}" class="contact-pill"><i class="fas fa-phone"></i> <span>${t.phone}</span></a>`);
    }
    if (social.facebook) {
        contacts.push(`<a href="https://facebook.com/${social.facebook}" target="_blank" class="contact-pill"><i class="fab fa-facebook"></i> <span>Facebook</span></a>`);
    }
    if (social.whatsapp) {
        contacts.push(`<a href="https://wa.me/${social.whatsapp}" target="_blank" class="contact-pill"><i class="fab fa-whatsapp"></i> <span>WhatsApp</span></a>`);
    }

    document.getElementById('contactGrid').innerHTML = contacts.join('');
}

function showLoading(show) {
    document.getElementById('loadingState').style.display = show ? 'block' : 'none';
    if (show) {
        document.getElementById('aboutContent').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
    }
}

function showEmptyState() {
    document.getElementById('emptyState').style.display = 'block';
    document.getElementById('aboutContent').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    fetchAboutData();
});

window.handleTeacherImgError = handleTeacherImgError;
</script>
@endpush
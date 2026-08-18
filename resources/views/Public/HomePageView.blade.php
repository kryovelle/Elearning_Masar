@extends('layouts.public')

@section('title', 'MASAR · Math & Physics with Prof. Karim Benyahia')

@section('content')
<main>
<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge"><i class="fas fa-chalkboard-teacher"></i> <span data-i18n="hero_eyebrow">One teacher · Every lesson personally verified</span></div>
        <h1>
            <span data-i18n="hero_h1_l1">Learn math & physics</span><br>
            <span data-i18n="hero_h1_l2_pre">directly with </span>
            <span class="highlight" data-i18n="hero_h1_l2_name">Prof</span> 
            <span class="highlight teacher_name"></span>
        </h1>
        <p data-i18n="hero_lead">No algorithms, no anonymous graders. Every course, every payment, every question is reviewed by Karim himself — the same teacher, from your first lesson to your final exam.</p>
        <div class="hero-actions">
            <a class="btn-large"  href="/Public/Courses"><i class="fas fa-rocket"></i> <span data-i18n="btn_browse" >Browse courses →</span></a>
            <span style="font-weight:500; color:#3a4f42;"><i class="fas fa-play-circle" style="color:#0e8a6d;"></i> <span data-i18n="btn_how">How enrollment works</span></span>
        </div>
    </div>
    <div class="hero-image">
        <div class="teacher-carmd">
            <img src="/storage/ui/home-page-avatar.jpg" width="400px" >
            <!--<div class="teacher-avatar"><i class="fas fa-user-circle"></i></div>
            <h3 class="teacher_name"></h3>
            <p><span data-i18n="math_physics_teacher">Math & Physics teacher</span> · <span id="teacherYears">12+</span> <span data-i18n="years">years</span></p>
            <span class="badge"><i class="fas fa-check-circle"></i> <span data-i18n="verified_teacher">Verified teacher</span></span>--->
        </div>
    </div>
</section>

<!-- FEATURED COURSES -->
<div class="section-title" id="courses"><i class="fas fa-book-open"></i> <span data-i18n="courses_eyebrow">Featured courses</span></div>
<div class="course-flex" id="courses-container" style="display:flex;flex-direction:row">
    <!-- Dynamically populated -->
</div>


<!-- HOW IT WORKS -->
<div class="section-title" id="how-it-works"><i class="fas fa-diagram-project"></i> <span data-i18n="how_eyebrow">How enrollment works</span></div>
<div class="steps-flex">
    <div class="step-item">
        <div class="step-num"> 1</div>
        <h4 data-i18n="step1_title">Choose your course</h4>
        <p data-i18n="step1_p">Create your account and pick the course that matches your level and exam.</p>
    </div>
    <div class="step-item">
        <div class="step-num">2</div>
        <h4 data-i18n="step2_title">Pay by CCP, upload the receipt</h4>
        <p data-i18n="step2_p">Transfer to Karim's CCP account, then upload a photo of your payment slip — takes less than two minutes.</p>
    </div>
    <div class="step-item">
        <div class="step-num"> 3</div>
        <h4 data-i18n="step3_title">Get access, personally confirmed</h4>
        <p data-i18n="step3_p">Karim checks every receipt himself. Once approved, your course unlocks immediately.</p>
    </div>
</div>
<div style="margin-bottom: 2rem; padding: 0.8rem 1.5rem; background: #fdf8ec; border-radius: 40px; text-align: center; border: 1px solid #f5e6bf;">
    <b data-i18n="seal_final_b">No automated approvals, ever.</b>
    <span data-i18n="seal_final_span">Every single enrollment on Masar is reviewed and confirmed by Karim in person — not a script.</span>
</div>

<!-- WHY CHOOSE MASAR -->
<div class="section-title" id="about"><i class="fas fa-check-circle"></i> <span data-i18n="why_choose_masar">Why choose MASAR</span></div>
<div class="why-flex">
    <div class="why-item"><i class="fas fa-user-tie"></i><h4 data-i18n="dedicated_teacher">One dedicated teacher</h4><p data-i18n="dedicated_teacher_desc">Every course taught personally by Prof. Karim</p></div>
    <div class="why-item"><i class="fas fa-clock"></i><h4 data-i18n="learn_pace">Learn at your pace</h4><p data-i18n="learn_pace_desc">Recorded video lessons and downloadable PDFs</p></div>
    <div class="why-item"><i class="fas fa-money-check-dollar"></i><h4 data-i18n="simple_ccp">Simple CCP payment</h4><p data-i18n="simple_ccp_desc">Pay offline, upload your receipt, get verified fast</p></div>
</div>

<!-- TESTIMONIALS -->
<div class="section-title"><i class="fas fa-comment-dots"></i> <span data-i18n="reviews_eyebrow">What students say</span></div>
<div class="testimonial-card">
    <i class="fas fa-quote-left"></i>
    <p data-i18n="t1_p">"The recorded sessions saved me before the Bac. I could rewatch the exact part I didn't understand as many times as I needed."</p>
    <div class="author">Amel Yousfi <span class="role">· <span data-i18n="t1_role">Terminale, Math</span></span></div>
</div>
<div class="testimonial-card" style="margin-top: 1rem;">
    <i class="fas fa-quote-left"></i>
    <p data-i18n="t2_p">"Payment felt safe because I knew a real person was checking it — not just an automatic system somewhere."</p>
    <div class="author">Mounir Belkacem <span class="role">· <span data-i18n="t2_role">1ère Année</span></span></div>
</div>
<div class="testimonial-card" style="margin-top: 1rem;">
    <i class="fas fa-quote-left"></i>
    <p data-i18n="t3_p">"Karim replies to questions the same day. It genuinely feels like a small classroom, not a big platform."</p>
    <div class="author">Sara Kaci <span class="role">· <span data-i18n="t3_role">Terminale, Physics</span></span></div>
</div>

<!-- CTA -->
<div style="background: #f7f8fd; border-radius: 28px; padding: 2.5rem; margin: 2rem 0; text-align: center; border: 1px solid #e9ecf9;">
    <h2 data-i18n="cta_h2" style="color: #131b3f;">Ready to start your course?</h2>
    <p data-i18n="cta_p" style="color: #4a5170; margin-bottom: 1.5rem;">Browse the catalogue and enroll in under five minutes.</p>
    <a class="btn-large" style="display: inline-block;" href="/Public/Courses"><i class="fas fa-rocket" ></i> <span data-i18n="cta_btn">Browse courses →</span></a>
</div>
</main>
@endsection

@push('scripts')
<script>
// Function to load dynamic data from API
async function loadHomeData() {
    try {
        const res = await fetch('/api/Public/getHomePageData');
        if (!res.ok) throw new Error('Failed to fetch home data');
        const data = await res.json();
        const teacher = data[0]?.teacher || {};
        const courses = data[0]?.featured_courses || [];

         const res2 = await fetch('/api/Public/getTeacherData');
        if (!res2.ok) throw new Error('Failed to fetch home data');

        // Update teacher info
        document.querySelectorAll('.teacher_name').forEach(el => {
            if (teacher.name) el.textContent = teacher.name;
        });
        
        // Render courses
        let container = document.getElementById('courses-container');
        if (container && courses.length > 0) {
            container.innerHTML = courses.map(c => `
               <div class="course-card">
    <div class="course-img">
        ${c.cover_image_url
            ? `<img src="${c.cover_image_url}" data-fallback-icon="${c.icon || 'fa-square-root-variable'}" style="width:100%;height:100%;object-fit:cover;border-radius:24px;" onerror="handleCourseImgError(this)">`
            : `<i class="fas ${c.icon || 'fa-square-root-variable'} fa-2x"></i>`}
    </div>
    <h4>${c.title || 'Course Name'}</h4>
    <div class="meta">${c.weeks_duration || '8 weeks'}</div>
    <div class="price">${c.price || '3000'} DA <span style="font-size:0.7rem;font-weight:400;color:#6d7395;" data-i18n="price_unit">${courses?.price_unit || '/ course'}</span></div>
    <span class="btn-details"><a data-i18n="details" href="/Public/CourseDetail/${c.id}"></a> <i class="fas fa-arrow-right"></i></span>
</div>
            `).join('');
        } else {
            container.innerHTML = `<p data-i18n="no_courses_available" style="text-align:center; color:#6d7395; width:100%;">${dict?.no_courses_available || 'No featured courses available right now'}</p>`;
        }

        // Reapply translations after dynamic content update
        const currentLang = document.documentElement.lang || 'en';
        if (typeof applyLanguage === 'function') {
            applyLanguage(currentLang);
        }

    } catch (error) {
        console.error('Error fetching home data:', error);
    }
}
function handleCourseImgError(img) {
    const icon = img.getAttribute('data-fallback-icon') || 'fa-square-root-variable';
    const wrapper = img.parentElement;
    wrapper.innerHTML = `<i class="fas ${icon} fa-2x"></i>`;
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadHomeData();
});
</script>
@endpush
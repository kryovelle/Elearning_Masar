<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MASAR · Math & Physics with Prof. Karim Benyahia')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    @stack('styles')
     @vite(['resources/css/app.css'])
</head>
<body>
    <div class="landing">
        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="logo-area">
                <div class="logo-icon"><i class="fas fa-square-root-variable"></i></div>
                <div class="logo-text">Ma<span>sar</span></div>
            </div>
            <div class="nav-links">
                <a href="{{ route('public.homepage') }}" class="{{ request()->routeIs('public.homepage') ? 'active' : '' }}" data-i18n="nav_homepage">Homepage</a>
                <a href="{{ route('public.courses') }}" class="{{ request()->routeIs('public.courses') ? 'active' : '' }}" data-i18n="nav_courses">Courses</a>
                <a href="{{ route('public.homepage') }}#how-it-works" class="{{ request()->routeIs('public.homepage') ? 'active' : '' }}" data-i18n="nav_how">How it works</a>
                <a href="{{ route('public.about') }}#about" class="{{ request()->routeIs('public.about') ? 'active' : '' }}" data-i18n="nav_about">About</a>
            </div>
            <div class="auth-buttons">
                <span class="btn-outline"><i class="fas fa-sign-in-alt"></i> <a data-i18n="nav_login" href="{{ route('public.login') }}">Log in</a></span>
                <span class="btn-primary"><a data-i18n="nav_register" href="{{ route('public.register') }}">Register</a></span>
                <!-- Language Switcher -->
                <div class="language-switcher" style="display: inline-flex; gap: 5px; align-items: center; margin-left: 10px;">
                    <button onclick="switchLanguage('en')" style="background: none; border: none; cursor: pointer; font-weight: {{ app()->getLocale() == 'en' ? 'bold' : 'normal' }}; color: #3454d1; padding: 5px;">EN</button>
                    <span style="color: #ccc;">|</span>
                    <button onclick="switchLanguage('ar')" style="background: none; border: none; cursor: pointer; font-weight: {{ app()->getLocale() == 'ar' ? 'bold' : 'normal' }}; color: #3454d1; padding: 5px;">AR</button>
                    <span style="color: #ccc;">|</span>
                    <button onclick="switchLanguage('fr')" style="background: none; border: none; cursor: pointer; font-weight: {{ app()->getLocale() == 'fr' ? 'bold' : 'normal' }}; color: #3454d1; padding: 5px;">FR</button>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        @yield('content')

        <!-- FOOTER -->
        <footer class="footer" >
            <div class="footer-col">
                <h5 data-i18n="footer_col_platform">Platform</h5>
                <div class="footer-links">
                    <a href="#" data-i18n="footer_tagline">A learning platform built around one teacher, one standard of quality, and a payment process you can trust.</a>
                </div>
            </div>
            <div class="footer-col">
                <h5 data-i18n="footer_col_contact">Contact</h5>
                <div class="footer-links contact-model">
                          <!------------------------------------------>
                </div>
            </div>
            <div class="footer-col">
                <h5 data-i18n="footer_col_payment">Payment</h5>
                <div class="footer-links payment-model">
                    <a><i class="fas fa-money-check-dollar"></i> <span data-i18n="footer_ccp_label">CCP:</span> <span id="footerCcpNumber"></span></a>
                    <a><i class="fas fa-user"></i> <span data-i18n="footer_name_label">Name:</span> <span id="footerCcpName"></span></a>
                </div>
            </div>
            <div class="footer-ccp">
                <i class="fas fa-bolt"></i>
                <span data-i18n="teacher_response_time"></span>
                <span></span>
            </div>
            <div class="footer-copy">
                <span data-i18n="footer_bottom_left">© 2026 Masar </span>
                <span data-i18n="footer_bottom_right">Made for students, checked by a teacher.</span>
            </div>
        </footer>
    </div>

    <script>
       async function loadFooterData(){
       const res = await fetch('/api/Public/getHomePageData');
       if(!res.ok) throw Error('Fetching Homepage Data Failed!');
       const data = await res.json();
       const social_links=data[0].social_links;
       const teacher=data[0].teacher;

       let contactModel=document.querySelector('.contact-model');
          contactModel.innerHTML=`<a href="mailto:${social_links.email}"><i class="fas fa-envelope"></i>&nbsp;${social_links.email}</a>
            <a href="tel:${teacher.phone}"><i class="fas fa-phone"></i>&nbsp;${teacher.phone}</a>
            <a href="https://facebook.com/${social_links.facebook}" target="_blank" rel="noopener"><i class="fab fa-facebook"></i>&nbsp;${social_links.facebook}</a>
            <a href="https://wa.me/${social_links.whatsapp}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i>&nbsp;${social_links.whatsapp}</a>`

      let paymentModel=document.querySelector('.payment-model');
      paymentModel.innerHTML=`
            <a><i class="fas fa-money-check-dollar "></i> <span data-i18n="footer_ccp_label" >CCP:</span> <span>${teacher.ccp_number}</span></a>
             <a><i class="fas fa-user"></i> <span data-i18n="footer_name_label" >Name:</span> <span >${teacher.ccp_name}</span></a>`
       }

document.addEventListener('DOMContentLoaded', function() {
    loadFooterData();
});
    </script>

    <!-- Translation Script -->
     @vite(['resources/js/app.js', 'resources/js/translations.js'])

    @stack('scripts')
</body>
</html>
<?php

use Illuminate\Support\Facades\Route;

// Public routes with proper naming
Route::get('/', function(){ return view('Public.HomePageView'); })->name('public.homepage');

Route::get('/Public', function(){ return view('Public.HomePageView'); })->name('public.home');

Route::get('/Public/HomePage', function(){ return view('Public.HomePageView'); })->name('public.homepage_full');

Route::get('/Public/Courses', function(){ return view('Public.CoursesListView'); })->name('public.courses');

Route::get('/Public/CourseDetail/{id}', function(){ return view('Public.CourseDetailView'); })->name('public.coursedetail');

Route::get('/Public/About', function(){ return view('Public.AboutView'); })->name('public.about');

Route::get('/Public/Register', function(){ return view('Public.RegisterView'); })->name('public.register');

Route::get('/Public/Login', function(){ return view('Public.LoginView'); })->name('public.login');









Route::get('/Student', function(){ return view('Student.DashboardView'); })->name('student.dashboard');

Route::get('/Student/Dashboard', function(){ return view('Student.DashboardView'); })->name('student.dashboard');

Route::get('/Student/Courses', function(){ return view('Student.CoursesView'); })->name('student.courses');

Route::get('/Student/CoursePlayer/{id}', function(){ return view('Student.CoursePlayerView'); })->name('student.courses');

Route::get('/Student/Payments', function(){ return view('Student.PaymentsView'); })->name('student.payments');

Route::get('/Student/Profile', function(){ return view('Student.ProfileView'); })->name('student.profile');







// Teacher routes with names
Route::get('/Teacher', function(){ return view('Teacher.DashboardView'); })->name('teacher.dashboard');

Route::get('/Teacher/Dashboard', function(){ return view('Teacher.DashboardView'); })->name('teacher.dashboard');

Route::get('/Teacher/Courses', function(){ return view('Teacher.CoursesView'); })->name('teacher.courses');

    // Course - View (Read-only)
Route::get('/Teacher/Course/{id}', function($id){ return view('Teacher.CourseView'); })->name('teacher.course.view');
    
    // Course - Edit Full (Modules & Lessons)
Route::get('/Teacher/Course/Edit/{id}', function($id){ return view('Teacher.CourseEdit'); })->name('teacher.course.edit');


Route::get('/Teacher/Students', function(){ return view('Teacher.StudentsView'); })->name('teacher.students');


Route::get('/Teacher/Payments', function(){ return view('Teacher.PaymentsView'); })->name('teacher.payments');

Route::get('/Teacher/Payments/{id}', function(){ return view('Teacher.PaymentsView'); })->name('teacher.payments');

Route::get('/Teacher/Profile', function(){ return view('Teacher.ProfileView'); })->name('teacher.profile');

// Teacher Login
Route::get('/Teacher/Login', function(){ return view('Teacher.LoginView'); })->name('teacher.login');


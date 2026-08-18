<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\CoursesListController;
use App\Http\Controllers\Public\LoginController;
use App\Http\Controllers\Public\LogoutController;
use App\Http\Controllers\Public\RegisterController;

use App\Http\Controllers\Student\StudentCoursesController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentPaymentsController;
use App\Http\Controllers\Student\StudentSettingsController;

use App\Http\Controllers\Teacher\TeacherCoursesController;
use App\Http\Controllers\Teacher\TeacherDashboardController;

use App\Http\Controllers\Teacher\TeacherPaymentsController;
use App\Http\Controllers\Teacher\TeacherSettingsController;

use App\Http\Controllers\Teacher\TeacherLoginController;

use App\Http\Controllers\Public\HomePageController;
use App\Http\Controllers\Teacher\TeacherStudentsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/************PUBLIC  *********************************************/
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/Public/getHomePageData', HomePageController::class . '@getHomePageData');
Route::get('/Public/getPublishedCourses',CoursesListController::class . '@getPublishedCourses');
Route::get('/Public/getCourseDataById',CoursesListController::class . '@getCourseDataById');
Route::post('/Public/enroll',CoursesListController::class . '@enroll')->middleware('auth:sanctum');
Route::get('/Public/getTeacherData',AboutController::class . '@getTeacherData');
Route::post('/Public/postRegister',RegisterController::class . '@postRegister')->middleware('throttle:5,1');
Route::post('/Public/postLogin' ,LoginController::class . '@postLogin')->middleware('throttle:5,1');
Route::post('/Public/mfa/verify' , LoginController::class . '@verifyMfa')->middleware('throttle:5,1');;
Route::post('/Public/verifyMfaWithRecoveryCode' , LoginController::class . '@verifyMfaWithRecoveryCode')->middleware('throttle:5,1');
Route::get('/Public/logout',LogoutController::class . '@logout')->middleware('auth:sanctum');
Route::get('/Public/logoutAll',LogoutController::class . '@logoutAll')->middleware('auth:sanctum');

/************Teacher *********************************************/
Route::post('/Teacher/postLogin/' ,TeacherLoginController::class . '@postLogin')->middleware('throttle:5,1') ;
Route::post('/Teacher/mfa/verify' , TeacherLoginController::class . '@verifyMfa')->middleware('throttle:5,1') ;
Route::post('/Teacher/verifyMfaWithRecoveryCode' , TeacherLoginController::class . '@verifyMfaWithRecoveryCode')->middleware('throttle:5,1');

Route::get('/Teacher/getTeacherDashboardData',TeacherDashboardController::class . '@getTeacherDashboardData')->middleware('auth:sanctum')->middleware('isTeacher');

Route::get('/Teacher/getTeacherCourses',TeacherCoursesController::class . '@getTeacherCourses') ->middleware('auth:sanctum')->middleware('isTeacher');
Route::post('/Teacher/createCourse',TeacherCoursesController::class . '@createCourse') ->middleware('auth:sanctum')->middleware('isTeacher');
Route::get('/Teacher/getCourseById',TeacherCoursesController::class .'@getCourseById') ->middleware('auth:sanctum')->middleware('isTeacher');
Route::post('/Teacher/editCourse',TeacherCoursesController::class .'@editCourse') ->middleware('auth:sanctum')->middleware('isTeacher');

Route::get('/Teacher/getStudents',TeacherStudentsController::class . '@getStudents' )->middleware('auth:sanctum')->middleware('isTeacher');
Route::get('/Teacher/getStudentById',TeacherStudentsController::class . '@getStudentById') ->middleware('auth:sanctum')->middleware('isTeacher');
Route::post('/Teacher/toggleBlockStudent',TeacherStudentsController::class . '@toggleBlockStudent') ->middleware('auth:sanctum')->middleware('isTeacher');
//

Route::get('/Teacher/getPayments',TeacherPaymentsController::class . '@getPayments') ->middleware('auth:sanctum');
Route::post('/Teacher/changePaymentStatus',TeacherPaymentsController::class . '@changePaymentStatus') ->middleware('auth:sanctum')->middleware('isTeacher');
Route::post('/Teacher/addTeacherNote',TeacherPaymentsController::class . '@addTeacherNote') ->middleware('auth:sanctum')->middleware('isTeacher');

Route::get('/Teacher/getSettingsData',TeacherSettingsController::class . '@getSettingsData')->middleware('auth:sanctum')->middleware('isTeacher');
Route::post('/Teacher/postSettingsData',TeacherSettingsController::class . '@postSettingsData')->middleware('auth:sanctum')->middleware('isTeacher');
Route::post('/Teacher/changePassword',TeacherSettingsController::class . '@changePassword')->middleware('auth:sanctum')->middleware('isTeacher')->middleware('throttle:5,1');
Route::post('/Teacher/setupMfa',TeacherSettingsController::class . '@setupMfa')->middleware('auth:sanctum');
Route::post('/Teacher/confirmMfa',TeacherSettingsController::class . '@confirmMfa')->middleware('auth:sanctum')->middleware('isTeacher')->middleware('throttle:5,1');
Route::post('/Teacher/disableMfa',TeacherSettingsController::class . '@disableMfa')->middleware('auth:sanctum')->middleware('isTeacher')->middleware('throttle:5,1');
Route::post('/Teacher/regenerateRecoveryCodes',TeacherSettingsController::class . '@regenerateRecoveryCodes')->middleware('isTeacher')->middleware('auth:sanctum')->middleware('throttle:5,1');

/*****************Student*************************************** */
Route::get('/Student/getDashboardData',StudentDashboardController::class . '@getDashboardData')->middleware('auth:sanctum'); 

Route::get('/Student/getApprovedCourses',StudentCoursesController::class . '@getApprovedCourses')->middleware('auth:sanctum');
Route::get('/Student/getCourseById',StudentCoursesController::class . '@getCourseById')->middleware('auth:sanctum');

Route::get('/Student/getPayments',StudentPaymentsController::class . '@getPayments')->middleware('auth:sanctum'); 
Route::get('/Student/getPaymentById',StudentPaymentsController::class . '@getPaymentById')->middleware('auth:sanctum');
Route::post('/Student/editPayment',StudentPaymentsController::class . '@editPayment')->middleware('auth:sanctum');

Route::get('/Student/getSettingsData',StudentSettingsController::class . '@getSettingsData')->middleware('auth:sanctum');
Route::post('/Student/postSettingsData',StudentSettingsController::class . '@postSettingsData')->middleware('auth:sanctum');
Route::post('/Student/changePassword',StudentSettingsController::class . '@changePassword')->middleware('auth:sanctum')->middleware('throttle:5,1');
Route::post('/Student/setupMfa',StudentSettingsController::class . '@setupMfa')->middleware('auth:sanctum')->middleware('throttle:5,1');
Route::post('/Student/confirmMfa',StudentSettingsController::class . '@confirmMfa')->middleware('auth:sanctum')->middleware('throttle:5,1');
Route::post('/Student/disableMfa',StudentSettingsController::class . '@disableMfa')->middleware('auth:sanctum')->middleware('throttle:5,1');
Route::post('/Student/regenerateRecoveryCodes',StudentSettingsController::class . '@regenerateRecoveryCodes')->middleware('auth:sanctum')->middleware('throttle:5,1');


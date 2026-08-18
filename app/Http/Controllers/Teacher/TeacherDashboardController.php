<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{

function getTeacherDashboardData(Request $request){
     $teacher=$request->User();

    $total_revenue = Payment::where('status', 'approved')->sum('amount');
    $total_courses_count = Course::count();
    $active_students_count = User::where('role', 'student')->count();

    $pending_payments_query = Payment::join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
        ->join('users', 'enrollments.student_id', '=', 'users.id')
        ->join('courses', 'enrollments.course_id', '=', 'courses.id')
        ->where('payments.status', 'pending')->orderByDesc('payments.submitted_at')
        ->select(
            'payments.id as payment_id',
            'users.name as student_name',
            'courses.title as course_title',
            'payments.submitted_at as date'
        );

    $pending_payments_count = $pending_payments_query->count();

    $pending_payments =  $pending_payments_query->take(4)->get();

    return response()->json([
        'stats_cards' => compact('active_students_count', 'pending_payments_count', 'total_courses_count', 'total_revenue'),
        'pending_payments' => $pending_payments,
        'teacher' => $teacher
    ]);
}
}

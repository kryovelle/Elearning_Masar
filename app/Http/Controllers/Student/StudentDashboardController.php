<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    function getDashboardData(Request $request){
        $user=$request->User();
        $recently_enrolled=Enrollment::where('student_id',$user->id)->join('courses','courses.id','=','enrollments.course_id')->leftjoin('payments','payments.enrollment_id','=','enrollments.id')->orderByDesc('enrollments.enrolled_at')->select('courses.title as course_title','enrollments.status as status','payments.teacher_note as teacher_note')->take(6)->get();
        return response()->json(compact('user','recently_enrolled'));
    }
}

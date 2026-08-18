<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;

class StudentCoursesController extends Controller
{
    function getApprovedCourses(Request $request){
    $user = $request->User();
    $approved_courses = Enrollment::where('student_id', $user->id)
        ->where('enrollments.status', 'approved')
        ->join('courses', 'courses.id', '=', 'enrollments.course_id')
        ->select(
            'courses.id as id',
            'courses.title as title',
            'courses.description as description',
            'courses.duration_weeks as duration_weeks',
            'courses.cover_image_url as cover_image_url'
        )->get();

    return response()->json(compact('user', 'approved_courses'));
}

function getCourseById(Request $request){
    $user = $request->User();

    $isEnrolled = Enrollment::where('student_id', $user->id)
        ->where('course_id', request('id'))
        ->where('status', 'approved')
        ->exists();

    if (!$isEnrolled) {
        return response()->json(['error' => 'You are not enrolled in this course'], 403);
    }

    $course_resp = Course::find(request('id'));

    if (!$course_resp) {
        return response()->json(['error' => 'Course not found'], 404);
    }

    $modules = Module::where('course_id', $course_resp['id'])->orderBy('order_index')->get();

    $modules_resp = [];
    foreach ($modules as $module) {
        $modules_resp[] = [
            'id'          => $module['id'],
            'course_id'   => $module['course_id'],
            'title'       => $module['title'],
            'order_index' => $module['order_index'],
            'created_at'  => $module['created_at'],
            'updated_at'  => $module['updated_at'],
            'lessons'     => Lesson::where('module_id', $module['id'])->orderBy('order_index')->get(),
        ];
    }

    return response()->json([
        'course'  => $course_resp,
        'modules' => $modules_resp,
    ]);
}
}

<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class TeacherStudentsController extends Controller
{
    function getStudents(Request $request){
        $teacher=$request->user();
        $students=User::with('enrollments')->where('users.role','student')->get()->makeVisible(['id','created_at']);
        return response()->json([
            'students'=>$students
        ]);
    }

   function getStudentById(Request $request){
    $teacher = $request->user();

    $request->validate([
        'student_id' => 'required|integer|exists:users,id'
    ]);

    $student = User::where('id', $request->student_id)
        ->where('role', 'student')
        ->with(['enrollments.payment', 'enrollments.course'])
        ->first();

    if (!$student) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    return response()->json(['student' => $student]);
}
function toggleBlockStudent(Request $request){
    $teacher = $request->user();

    $validated = $request->validate([
        'student_id' => 'required|integer|exists:users,id',
        'block' => 'required|boolean'
    ]);

    $student = User::where('id', $validated['student_id'])
        ->where('role', 'student')
        ->first();

    if (!$student) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    $student->update([
        'is_blocked' => $validated['block']
    ]);

    $message = $validated['block'] ? 'Student blocked successfully!' : 'Student unblocked successfully!';
    return response()->json(['success' => $message]);
}
}
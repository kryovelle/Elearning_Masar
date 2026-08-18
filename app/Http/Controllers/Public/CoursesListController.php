<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Payment;
use App\Models\User;
use Error;
use ErrorException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CoursesListController extends Controller
{
    function getPublishedCourses(Request $request){
        $courses=Course::query()->where('status','published');
    
        $courses->when($request->filled('title'),function($c) use($request){
            $title=$request->input('title');
            $c->where('title','like',"%{$title}%");
        });
        $courses->when($request->filled('price'),function($c) use($request){
            $price=$request->input('price');
            $c->where('price','<=',$price);
        });

       
        $courses=$courses->get();
        return response()->json(
            compact('courses')
        );

    }

    function getCourseDataById(Request $request){
    $teacher=User::where('role','teacher')->first();
    $ccp_number=$teacher->ccp_number;
    $ccp_name=$teacher->ccp_name;
    $id=request('id');
    $course=Course::find($id);
    $modules=Module::where('course_id',$id)->orderBy('order_index')->get();
    return response()->json(compact('course','modules','ccp_number','ccp_name'));
    }




public function enroll(Request $request)
{
    $user = $request->user();
    if($user->role!='student'){
        return response()->json(['error'=>'Forbidden',403]);
    }

    $request->validate([
        'course_id' => 'required|exists:courses,id',
        'receipt_image_url' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        'student_note' => 'nullable|string|max:100',
    ]);

    $course = Course::find($request->course_id);

    if (!$course) {
        return response()->json([
            'error' => 'This course is no longer available.'
        ], 404);
    }

    try {

        $enrollment = DB::transaction(function () use ($request, $user, $course) {

            // Create enrollment
            $enrollment = Enrollment::create([
                'course_id' => $course->id,
                'student_id' => $user->id,
                'status' => 'pending',
                'enrolled_at' => now()
            ]);


             $receipt = $request->file('receipt_image_url');

            $receiptPath = $receipt->store('receipts', 'public');

            // Save payment
            $payment = Payment::create([
                'enrollment_id' => $enrollment->id,
                'amount' => $course->price,
                'status' => 'pending',
                'student_note' => $request->student_note,
                'submitted_at'=>now(),
                 'receipt_image_url' => Storage::url($receiptPath),
            ]);

            return $enrollment;
        });

        return response()->json([
            'success' => 'Enrollment submitted successfully!'
        ], 201);

    } catch (QueryException $e) {

        // MySQL / MariaDB duplicate unique constraint
        if ($e->getCode() === '23000' && $e->errorInfo[1] === 1062) {

            return response()->json([
                'error' => 'You are already enrolled in this course.'
            ], 409);
        }

        // Re-throw unexpected database errors
        throw $e;
    }
}
}

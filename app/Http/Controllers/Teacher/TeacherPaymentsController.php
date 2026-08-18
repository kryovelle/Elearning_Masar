<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherPaymentsController extends Controller
{
    function getPayments(Request $request){
        $teacher = $request->user();

        $query = Enrollment::join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('users', 'users.id', '=', 'enrollments.student_id')
            ->join('payments', 'payments.enrollment_id', '=', 'enrollments.id');

        $query->when($request->filled('course_id'), function($q) use($request){
            $q->where('enrollments.course_id', $request->input('course_id'));
        });

        $query->when($request->filled('student_name'), function($q) use($request){
            $q->where('users.name', 'like', "%{$request->input('student_name')}%");
        });

        $query->when($request->filled('filter'), function($q) use($request){
            $q->where('payments.status', $request->input('filter'));
        });

        $payments = $query->orderByDesc('payments.submitted_at')->select(
            'payments.id as payment_id',
            'users.name as student',
            'courses.title as course',
            'payments.submitted_at as date',
            'payments.amount as amount',
            'payments.receipt_image_url as receipt_image_url',
            'payments.student_note as student_note',
            'payments.teacher_note as teacher_note',
            'payments.status as status'
        )->get();

        return response()->json(compact('payments'));
    }

    function changePaymentStatus(Request $request){
        $teacher = $request->user();

        $validated = $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'status' => 'required|string|in:rejected,pending,approved',
        ]);

        DB::transaction(function () use ($validated) {
            $payment = Payment::findOrFail($validated['payment_id']);
            $enrollment = Enrollment::findOrFail($payment->enrollment_id);

            $enrollment->update([
                'status' => $validated['status'],
            ]);

            $payment->update([
                'status' => $validated['status'],
                'reviewed_at' => now(),
            ]);

            if ($validated['status'] === 'approved') {
                $enrollment->update([
                    'approved_at' => now(),
                ]);
            }
        });

        return response()->json(['success' => 'Status Updated!']);
    }

    function addTeacherNote(Request $request){
        $teacher = $request->user();

        $validated = $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'teacher_note' => 'required|string|max:1000',
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);
        $payment->update([
            'teacher_note' => $validated['teacher_note'],
        ]);

        return response()->json(['success' => 'Note added successfully!']);
    }
}
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentPaymentsController extends Controller
{
    function getPayments(Request $request){
        $user = $request->User();

        $payments = Enrollment::where('enrollments.student_id', $user->id)
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('payments', 'payments.enrollment_id', '=', 'enrollments.id')
            ->select(
                'courses.title as course',
                'payments.amount as amount',
                'payments.submitted_at as date',
                'payments.status as status',
                'payments.id as payment_id'
            )->get();

        return response()->json(compact('user', 'payments'));
    }

    function getPaymentById(Request $request){
        $user = $request->User();

        $payment = Payment::where('payments.id', request('payment_id'))
            ->join('enrollments', 'enrollments.id', '=', 'payments.enrollment_id')
            ->where('enrollments.student_id', $user->id)
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->select(
                'courses.title as course',
                'payments.amount as amount',
                'payments.submitted_at as submitted_at',
                'payments.status as status',
                'payments.receipt_image_url as receipt_image_url',
                'payments.student_note as ur_note',
                'payments.teacher_note as teacher_note',
                'payments.id as payments_id'
            )->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json(compact('user', 'payment'));
    }

    function editPayment(Request $request){
        $user = $request->User();

        $request->validate([
            'payment_id' => 'required',
            'student_note' => 'nullable|string|max:500',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $payment = Payment::where('payments.id', request('payment_id'))
            ->join('enrollments', 'enrollments.id', '=', 'payments.enrollment_id')
            ->where('enrollments.student_id', $user->id)
            ->select('payments.*')
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        if (!in_array($payment->status, ['pending', 'rejected'])) {
            return response()->json(['error' => 'This payment can no longer be edited'], 403);
        }

        $path = $request->file('receipt_image')->store('receipts', 'public');

        $payment->update([
            'receipt_image_url' => Storage::url($path),
            'student_note' => request('student_note'),
            'status' => 'pending',
            'teacher_note' => null,
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => 'Payment updated successfully']);
    }
}
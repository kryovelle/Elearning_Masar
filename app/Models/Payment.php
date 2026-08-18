<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
       public $timestamps = false;
    protected $table = 'payments';

protected $fillable = [
    'enrollment_id',
    'amount',
    'receipt_image_url',
    'student_note',
    'teacher_note',
    'status',
    'submitted_at',
    'reviewed_at',
];

function enrollment(){
    return $this->hasOne(Enrollment::class,'enrollment_id');
}
}

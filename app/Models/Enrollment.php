<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    public $timestamps = false;
    protected $table = 'enrollments';

protected $fillable = [
    'student_id',
    'course_id',
    'status',
    'enrolled_at',
    'approved_at',
];
function user(){
    return $this->belongsTo(User::class,'student_id');
}
function payment(){
    return $this->hasOne(Payment::class,'enrollment_id');
}

function course(){
    return $this->belongsTo(Course::class,'course_id');
}
}

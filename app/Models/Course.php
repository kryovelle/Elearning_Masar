<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $hidden=[
        'teacher_id'
    ];

protected $fillable = [
    'teacher_id',
    'title',
    'description',
    'price',
    'duration_weeks',
    'cover_image_url',
    'status',
    'ccp_number_override',
    'ccp_name_override',
    'featured'
];

public function modules()
{
    return $this->hasMany(Module::class)->orderBy('order_index');
}

public function enrollments(){
    return $this->hasMany(Enrollment::class,'course_id');
}
}

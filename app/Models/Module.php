<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

protected $fillable = [
    'course_id',
    'title',
    'order_index',
];

public function course()
{
    return $this->belongsTo(Course::class);
}

public function lessons()
{
    return $this->hasMany(Lesson::class)->orderBy('order_index');
}
}

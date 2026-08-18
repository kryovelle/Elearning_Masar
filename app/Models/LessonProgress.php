<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

protected $fillable = [
    'student_id',
    'lesson_id',
    'completed',
    'completed_at',
];
}

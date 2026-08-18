<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $table = 'lessons';

protected $fillable = [
    'module_id',
    'title',
    'type',
    'content_url',
    'order_index',
];
public function module()
{
    return $this->belongsTo(Module::class);
}
}

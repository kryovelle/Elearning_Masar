<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    function getTeacherData(){
        $teacher = User::where('role', 'teacher')->get()->makeHidden(['id', 'role']);
        return response()->json(compact('teacher'));
    }
}

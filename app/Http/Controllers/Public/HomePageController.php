<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SocialLinks;
use App\Models\User;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    function getHomePageData(){
        $teacher=User::where('role','=','teacher')->first()->makeHidden(['id','role']);
        $social_links=SocialLinks::first()->makeHidden(['created_at','updated_at']);
        $featured_courses=Course::where('featured',1)->
        where('status','published')->take(4)->get()->makeHidden('teacher_id');
        return response()->json([
            compact(
                'teacher',
                'social_links',
                'featured_courses'
            )
        ]);
        }
}
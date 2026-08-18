<?php

namespace App\Http\Controllers\Public;

use App\Models\Session;
use Illuminate\Http\Request;



class LogoutController
{
  function logout(Request $request){
    $user=$request->user();

    if($request->header('X-Client-Type')==='mobile'){
      $request->currentAccessToken()->delete();
    }else{

    $request->session()->invalidate();
    $request->session()->regenerateToken();
    }

    return response()->json(['success'=>'You have successfully logged out from this device']);

  }

  function logoutAll(Request $request){
    $user= $request->user();
    $user->tokens()->delete();
    if($request->hasSession()){
      $request->session()->invalidate();
      $request->session()->regenerateToken();
    }

    //delete all the sessions
    Session::where('user_id', $user->id)->delete();
   return response()->json([
        'success' => 'You have successfully logged out from all connected devices'
    ]);

    }

}
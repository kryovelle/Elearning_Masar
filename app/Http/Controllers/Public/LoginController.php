<?php

namespace App\Http\Controllers\Public;


use Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\User;
use Cache;
use Hash;
use Str;
use PragmaRX\Google2FA\Google2FA;

class LoginController
{
    function postLogin(Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);
       
        /*Rate limitter process */
        $key='login'.$request->ip().'|'.$request->email;
        if(RateLimiter::tooManyAttempts($key,6)){
            $seconds=RateLimiter::availableIn($key);
            return response()->json([
                'error'=>"Too many attempts, try in {$seconds} seconds"
            ]);
        }
        RateLimiter::hit($key);
    

        $user=User::where('email',request('email'))->first();
        if(!$user || !Hash::check(request('password'),$user->password) || $user->role!='student'){
            return response()->json(['error'=>'Email or password are incorrect'],401);
        }
        if($user->is_blocked){
            return response()->json(['error'=>'Your account have been blocked'],403);
        }


        if($user->mfa_enabled){
            $mfaToken=Str::random(64);
             Cache::put("mfa_pending:{$mfaToken}", $user->id, now()->addMinutes(6));
              return response()->json([
            'mfa_required' => true,
            'mfa_token' => $mfaToken,
        ]);
        }

        
        RateLimiter::clear($key);

        if($request->header('X-Client-Type')==='mobile'){
        $token=$user->createToken('auth_token')->plainTextToken;

        return response()->json([
        'mfa_required' => false,
        'token' => $token
       ]);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return response()->json([
        'mfa_required' => false]);
    }



public function verifyMfa(Request $request)
{
    $request->validate([
        'mfa_token' => 'required|string',
        'code' => 'required|digits:6',
    ]);
    

    $userId = Cache::get("mfa_pending:{$request->mfa_token}");

    if (! $userId) {
        return response()->json(['error' => 'MFA session expired'], 401);
    }

    $user = User::findOrFail($userId);

    $mfakey='mfa'.$request->ip().'|'.$user->email;
    if(RateLimiter::tooManyAttempts($mfakey,6)){
        $seconds=RateLimiter::availableIn($mfakey);
        return response()->json(['error'=>"Too many attempts, try in {$seconds} seconds"]);
    };

    RateLimiter::hit($mfakey);

    $google2fa = new Google2FA();

    $valid = $google2fa->verifyKey(
        $user->mfa_secret,   
        $request->code,     
        1                 
    );

    if (! $valid) {
        return response()->json(['error' => 'Invalid code'], 422);
    }

    Cache::forget("mfa_pending:{$request->mfa_token}");

    $loginkey='login'.$request->ip().'|'.$user->email;
    RateLimiter::clear($loginkey);
    RateLimiter::clear($mfakey);

    if($request->header('X-Client-Type')==='mobile'){
        $token=$user->createToken('auth_token')->plainTextToken;

        return response()->json([
        'success'=>'Logged in successfully!',
        'token' => $token
       ]);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return response()->json([
        'success'=>'Logged in successfully!']);
}

    public function verifyMfaWithRecoveryCode(Request $request)
  {
    $request->validate([
        'mfa_token' => 'required|string',
        'recovery_code' => 'required|string',
    ]);

    $userId = Cache::get("mfa_pending:{$request->mfa_token}");

    if (! $userId) {
        return response()->json(['error' => 'MFA session expired'], 401);
    }

    $user = User::findOrFail($userId);
    $codes = $user->mfa_recovery_codes ?? [];

    $reckey='recovery_code'.$request->ip().'|'.$user->email;
    if(RateLimiter::tooManyAttempts($reckey,6)){
        $seconds=RateLimiter::availableIn($reckey);
        return response()->json(['error'=>"Too many attempts, Try in {$seconds} seconds"]
        );
    }
    RateLimiter::hit($reckey);

    $matchedIndex = null;

    foreach ($codes as $index => $hashedCode) {
        if (Hash::check($request->recovery_code, $hashedCode)) {
            $matchedIndex = $index;
            break;
        }
    }

    if ($matchedIndex === null) {
        return response()->json(['error' => 'Invalid or already-used recovery code'], 422);
    }

    // burn the used code so it can't be reused
    unset($codes[$matchedIndex]);
    $user->update(['mfa_recovery_codes' => array_values($codes)]);

    Cache::forget("mfa_pending:{$request->mfa_token}");
    RateLimiter::clear($reckey);

    $loginkey='login'.$request->ip().'|'.$user->email;
    RateLimiter::clear($loginkey);

    $mfakey='mfa'.$request->ip().'|'.$user->email;
    RateLimiter::clear($mfakey);


    if($request->header('X-Client-Type')==='mobile'){
        $token=$user->createToken('auth_token')->plainTextToken;

        return response()->json([
        'success'=>'Logged in successfully!',
        'token' => $token
       ]);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return response()->json([
        'success'=>'Logged in successfully!']);
}




}

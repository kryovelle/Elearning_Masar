<?php

namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
class TeacherSettingsController extends Controller
{
function getSettingsData(Request $request){
    $user = $request->User();
      return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'photo_url' => $user->photo_url,
            'mfa_enabled' => $user->mfa_enabled,
            'bio'=>$user->bio,
            'ccp_number'=>$user->ccp_number,
            'ccp_name'=>$user->ccp_name
        ]
    ]);
}

function postSettingsData(Request $request){
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $request->user()->id,
        'phone' => 'required|string|max:20',
        'photo' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
        'bio'=>'nullable|string|max:100',
        'ccp_number'=>'nullable|string|max:30',
        'ccp_name'=>'nullable|string|max:30'
    ], [
        'email.unique' => 'This email already exists!',
    ]);

    $user = $request->User();

    $data = [
        'name'  => request('name'),
        'email' => request('email'),
        'phone' => request('phone'),
        'bio' => request('bio'),
        'ccp_name' => request('ccp_name'),
        'ccp_number' => request('ccp_number'),
    ];

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('avatars', 'public');
        $data['photo_url'] = \Illuminate\Support\Facades\Storage::url($path);
    }

    $user->update($data);

    return response()->json(['success' => 'Settings Data saved successfully!']);
}

function changePassword(Request $request){
    $request->validate([
        'curr_password' => 'required',
        'new_password' => [
            'required', 'string', 'min:8', 'confirmed',
            'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[^a-zA-Z0-9]/',
        ],
    ], [
        'curr_password'=>'The current password is required',
        'new_password.min' => 'Password must be at least 8 characters',
        'new_password.confirmed' => 'Passwords do not match',
        'new_password.regex' => 'Password must include an uppercase letter, a lowercase letter, a number, and a symbol',
    ]);

    $user = $request->User();

    if (!Hash::check(request('curr_password'), $user->password)) {
        return response()->json(['error' => 'The current password is incorrect!'],422);
    }

    $user->update(['password' => Hash::make(request('new_password'))]);

    return response()->json(['success' => 'Password changed successfully!']);
}

public function setupMfa(Request $request)
{
    $user = $request->user(); // must be authenticated already

    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();

    // store as PENDING — not saved to the user yet
    Cache::put("mfa_setup_pending:{$user->id}", $secret, now()->addMinutes(10));

    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Masar',
        $user->email,
        $secret
    );

    $renderer = new ImageRenderer(
        new RendererStyle(200),
        new SvgImageBackEnd()
    );
    $writer = new Writer($renderer);
    $qrSvg = $writer->writeString($qrCodeUrl);

    return response()->json([
        'secret' => $secret,
        'qr_code' => 'data:image/svg+xml;base64,' . base64_encode($qrSvg),
    ]);
}

public function confirmMfa(Request $request)
{
    $request->validate([
        'code' => 'required|digits:6',
    ],[
        'code.digits'=>'Please Enter 6 digits!'
    ]);

    $user = $request->user();

    $secret = Cache::get("mfa_setup_pending:{$user->id}");

    if (! $secret) {
        return response()->json(['error' => 'Setup session expired, please restart'], 422);
    }

    $google2fa = new Google2FA();

    if (! $google2fa->verifyKey($secret, $request->code, 1)) {
        return response()->json(['error' => 'Invalid code'], 422);
    }

    // generate 8 plain-text codes to show the user ONCE
    $plainCodes = collect(range(1, 8))->map(function () {
        return Str::random(4) . '-' . Str::random(4); // e.g. "aB3x-9kLm"
    })->toArray();

    // store only HASHED versions — never save plain codes to DB
    $hashedCodes = array_map(fn ($code) => Hash::make($code), $plainCodes);

    $user->update([
        'mfa_secret' => $secret,
        'mfa_enabled' => true,
        'mfa_recovery_codes' => $hashedCodes,
    ]);

    Cache::forget("mfa_setup_pending:{$user->id}");

    return response()->json([
        'success' => 'MFA enabled successfully',
        'recovery_codes' => $plainCodes, // shown once — user must save these now
    ]);
}

public function disableMfa(Request $request)
{
    $request->validate([
        'password' => 'required',
    ]);

    $user = $request->user();

    if (! Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Incorrect password'], 422);
    }

    $user->update([
        'mfa_enabled' => false,
        'mfa_secret' => null,
        'mfa_recovery_codes' => null,
    ]);

    return response()->json(['success' => 'MFA disabled']);
}
public function regenerateRecoveryCodes(Request $request)
{
    $request->validate([
        'password' => 'required',
    ]);

    $user = $request->user();

    if (! Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Incorrect password'], 422);
    }

    if (! $user->mfa_enabled) {
        return response()->json(['error' => 'MFA is not enabled'], 422);
    }

    $plainCodes = collect(range(1, 8))->map(function () {
        return Str::random(4) . '-' . Str::random(4);
    })->toArray();

    $hashedCodes = array_map(fn ($code) => Hash::make($code), $plainCodes);

    $user->update(['mfa_recovery_codes' => $hashedCodes]);

    return response()->json([
        'success' => 'Recovery codes regenerated',
        'recovery_codes' => $plainCodes,
    ]);
}

}

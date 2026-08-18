<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    function postRegister(Request $request)
    {
        $request->validate( [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
        ], [
            'email.unique' => 'This email is already registered',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Passwords do not match',
            'password.regex' => 'Password must include an uppercase letter, a lowercase letter, a number, and a symbol',
        ]);


        $user = User::create([
            'name'     => request('name'),
            'email'    => request('email'),
            'phone'    => request('phone'),
            'password' => Hash::make(request('password')),
            'role'     => 'student',
            'mfa_enabled'=>0
        ]);

        return response()->json([
            'success' => 'Account created successfully! Please log in to continue',
        ]);
    }
}
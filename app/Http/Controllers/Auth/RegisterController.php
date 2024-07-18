<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Create a new RegisterController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create the user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => false,
        ]);

        // Generate a verification code
        $verificationCode = rand(1000, 9999);

        // Save the verification code
        EmailVerification::create([
            'user_id' => $user->id,
            'code' => $verificationCode,
        ]);

        // Send verification code via email
        Mail::to($user->email)->send(new \App\Mail\VerifyEmail($verificationCode));

        // Return response
        return response()->json(['message' => 'User registered. Please verify your email.'], 201);
    }
}

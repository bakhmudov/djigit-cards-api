<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
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

            // Save or update the verification code
            EmailVerification::updateOrCreate(
                ['user_id' => $user->id], // Condition
                ['code' => $verificationCode] // Update or insert this code
            );

            // Send verification code via email
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($verificationCode));

            // Log the successful registration
            Log::info('User registered successfully', ['user_id' => $user->id]);

            // Return response
            return response()->json(['status' => 'User registered. Please verify your email.'], 201);

        } catch (\Exception $e) {
            Log::error('Error during registration', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Registration failed, please try again.'], 500);
        }
    }

    /**
     * Resend the email verification code.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerificationCode(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'email' => 'required|string|email|exists:users,email',
            ]);

            // Find the user
            $user = User::where('email', $request->email)->first();

            if ($user->is_active) {
                return response()->json(['message' => 'User is already verified.'], 400);
            }

            // Generate a new verification code
            $verificationCode = rand(1000, 9999);

            // Update or create a new verification code
            $verification = EmailVerification::updateOrCreate(
                ['user_id' => $user->id],
                ['code' => $verificationCode]
            );

            // Resend verification code via email
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($verificationCode));

            // Log the resend action
            Log::info('Verification code resent successfully', ['user_id' => $user->id]);

            return response()->json(['status' => 'Verification code resent. Please check your email.'], 200);

        } catch (\Exception $e) {
            Log::error('Error during resending verification code', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to resend verification code, please try again.'], 500);
        }
    }
}

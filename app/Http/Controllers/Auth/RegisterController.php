<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->is_active) {
                return response()->json(['error' => 'User already registered and verified'], 400);
            } else {
                // User exists but not verified, resend verification code
                return $this->resendVerificationCode($user);
            }
        }

        // Create new user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => false,
        ]);

        return $this->sendVerificationCode($user);
    }

    protected function sendVerificationCode($user)
    {
        $verificationCode = rand(1000, 9999);

        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $verificationCode,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        Mail::to($user->email)->send(new \App\Mail\VerifyEmail($verificationCode));

        return response()->json(['status' => 'Verification code sent. Please check your email.'], 200);
    }

    protected function resendVerificationCode($user)
    {
        // Delete any existing verification code
        EmailVerification::where('user_id', $user->id)->delete();

        return $this->sendVerificationCode($user);
    }

    public function resendVerificationCodeManually(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->is_active) {
            return response()->json(['error' => 'User already verified'], 400);
        }

        return $this->resendVerificationCode($user);
    }
}
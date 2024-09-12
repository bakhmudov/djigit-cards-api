<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificationController extends Controller
{
    /**
     * Verify a user's email address and automatically log them in.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|exists:users,email',
            'code' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Find the verification record
        $verification = EmailVerification::where('code', $request->code)
            ->where('user_id', $user->id)
            ->first();

        if (!$verification) {
            return response()->json(['error' => 'Invalid verification code'], 400);
        }

        // Activate the user
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->save();

        // Delete the verification record
        $verification->delete();

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Return response with token
        return response()->json([
            'message' => 'Email verified successfully',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
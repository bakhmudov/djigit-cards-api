<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Verify a user's email address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|exists:users',
            'code' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Find the verification record
        $verification = EmailVerification::where('code', $request->code)
            ->whereHas('user', function ($query) use ($request) {
                $query->where('email', $request->email);
            })->first();

        if (!$verification) {
            return response()->json(['error' => 'Invalid verification code'], 400);
        }

        // Activate the user
        $user = $verification->user;
        $user->is_active = true;
        $user->save();

        // Delete the verification record
        $verification->delete();

        // Return response
        return response()->json(['message' => 'Email verified successfully']);
    }
}

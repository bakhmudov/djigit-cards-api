<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    /**
     * Handle sending reset code.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function sendResetCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();
        $code = random_int(1000, 9999);

        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'created_at' => now()]
        );

        Mail::raw("Your password reset code is: $code", function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Password Reset Code');
        });

        return response()->json(['data' => ['status' => 'Code sent successfully']], 200);
    }

    /**
     * Handle confirming reset code.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmResetCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $reset = EmailVerification::where('code', $request->code)->first();

        if (!$reset) {
            return response()->json(['error' => 'Invalid code'], 400);
        }

        return response()->json(['data' => ['status' => 'Code confirmed']], 200);
    }

    /**
     * Handle saving new password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveNewPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'confirmPassword' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Find the email associated with the reset code
        $reset = EmailVerification::where('code', $request->code)->first();

        if (!$reset) {
            return response()->json(['error' => 'Invalid or expired reset code'], 400);
        }

        $user = User::where('id', $reset->user_id)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        $reset->delete();

        return response()->json(['data' => ['status' => 'Password reset successfully']], 200);
    }
}


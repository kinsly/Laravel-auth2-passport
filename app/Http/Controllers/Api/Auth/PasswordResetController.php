<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * @method post
     * Sending password reset link to mail
     */
    public function sendResetRequest(Request $request):JsonResponse
    {
        $request->validate(['email' => 'required|email']);
 
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status === Password::RESET_LINK_SENT)
        {
            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'Verification email send success'
            ], 201);
        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 400,
                'message' => $status 
            ],400);
        }

    }
    
    
    /**
     * @method post
     * Update new password
     */
    public function restPassword(Request $request):JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
     
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );

        if($status === Password::PASSWORD_RESET)
        {
            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'Password reset successful'
            ]);

        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 400,
                'message' => $status
            ],400);
            
        }
    }

}

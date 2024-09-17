<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /**
     * @Method Get
     * Verify email address once user click on the link send via email
     */
    public function verifyEmail(EmailVerificationRequest $request):JsonResponse
    {
        
        $request->fulfill();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'Email verification success.'
        ], 201);
    }
}

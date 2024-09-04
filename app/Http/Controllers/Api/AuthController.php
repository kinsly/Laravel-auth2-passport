<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * User Registration
     */
    public function register(RegisterRequest $request):JsonResponse
    {
        $userData = $request->validated();
        $userData['email_verified_at'] = now();

        $user = User::create($userData);

        $response = Http::post(env('APP_URL').'/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'username' => $userData['email'],
            'password' => $userData['password'],
            'scope' => '',
        ]);

        $user['token'] = $response->json();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'User has been registered successfully.',
            'data' => $user,
        ], 201);
    }
}

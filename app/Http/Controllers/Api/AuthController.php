<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * User Registration
     * @param RegisterRequest $request
    */
    public function register(RegisterRequest $request):JsonResponse
    {   
        //validate user data for registration
        $userData = $request->validated();
        
        //Manually verify email address till mail verification enabled
        $userData['email_verified_at'] = now();

        //Create new user
        $user = User::create($userData);
        
        /**
         * Catch errors like invalid code, secrete, sever error on auth2.0, etc.
         * Used to catch auth2.0 serrors and rollback created user
        **/
        try{
            $response = Http::post(env('APP_URL').'/oauth/token', [
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
                'username' => $userData['email'],
                'password' => $userData['password'],
                'scope' => '',
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to obtain access token.');
            }

        }catch(Exception $e){
            /**
             * Rollback transaction. 
             * We cannot use db:rollback function. Because we need user created to get access token.
             */
            if (isset($user)) {
                $user->delete();
            }

            // Log the error
            Log::error('User registration failed: ' . $e->getMessage());

            //Send error while getting access tokens
            return response()->json([
                'success' => false,
                'statusCode' => 500,
                'message' => $e->getMessage()
            ],500);
        }//end of try-catch

        $user['token'] = $response->json();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'User has been registered successfully.',
            'data' => $user,
        ], 201);
    }

    /**
     * User Login
     * @param LoginRequest $request
     */
    public function login(LoginRequest $request):JsonResponse
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            
            $response = Http::post(env('APP_URL') . '/oauth/token', [
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
                'username' => $request->email,
                'password' => $request->password,
                'scope' => '',
            ]);

            //Send error while getting access tokens
            if ($response->failed()) {               
                return response()->json([
                    'success' => false,
                    'statusCode' => 500,
                    'message' =>"Internal server error!"
                ],500);
            }

            //attach access token to user object
            $user['token'] = $response->json();

            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => 'User has been logged successfully.',
                'data' => $user,
            ], 200);
        //Invalid user credentials
        } else {
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => 'Unauthorized.',
                'errors' => 'Unauthorized',
            ], 401);
        }
    }

    /**
     * User Profile
     */
    public function profile():JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Authenticated user info.',
            'data' => $user,
        ], 200);
    }

 
    /**
     * RefreshToken
     */
    public function refreshToken(RefreshTokenRequest $request):JsonResponse
    {
        $response = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'scope' => '',
        ]);

        if($response->failed()){
            return response()->json([
                'success' => false,
                'statusCode' => 500,
                'message' =>"Internal server error!"
            ],500);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Refreshed token.',
            'data' => $response->json(),
        ], 200);
    }

    /**
     * User Logout
     */
    public function logout():JsonResponse
    {
        /** @var App/Model/User */
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 204,
            'message' => 'Logged out successfully.',
        ], 204);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the HTTP response for the OAuth token request
        Http::fake([
            env('APP_URL').'/oauth/token' => Http::response([
                'access_token' => 'fake-access-token',
                'refresh_token' => 'fake-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        //Registering user for testing
        $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

    }

    /**
     * Test user login with actual credentials
     */
    public function test_user_login_with_actual_credentials(): void
    {
        $response = $this->postJson(env('APP_URL').'/api/login',[
            'email' =>'user@gmail.com',
            'password' => 'password'
        ]);

        $response->assertJson([
            "success" => true,
            "statusCode" => 200,
            "message"=> "User has been logged successfully.",
                "data" => [
                    "name"=> "user",
                    "email"=> "user@gmail.com",
                    "email_verified_at" => null,
                    "token" => [
                                "access_token" => "fake-access-token",
                                "refresh_token" => "fake-refresh-token"
                            ]
                ]
            
        ]);

        $response->assertStatus(200);
    }

    /**
     * Login with invalid email
     */
    public function test_login_with_invalid_email():void
    {
        $response = $this->postJson(env('APP_URL').'/api/login',[
            'email' =>'invalid@gmail.com',
            'password' => 'password'
        ]);

        $response->assertJson([
            "success"=> false,
            "statusCode"=> 401,
            "message"=> "Unauthorized.",
            "errors"=> "Unauthorized"    
        ]);

        $response->assertStatus(401);
    }

    /**
     * Login with invalid password
     */
    public function test_login_with_invalid_password():void
    {
        $response = $this->postJson(env('APP_URL').'/api/login',[
            'email' =>'user@gmail.com',
            'password' => 'invalid-password'
        ]);

        $response->assertJson([
                "success"=> false,
                "statusCode"=> 401,
                "message"=> "Unauthorized.",
                "errors"=> "Unauthorized"    
            ]);

        $response->assertStatus(401);
    }
}

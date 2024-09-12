<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;

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

    }
    /**
     * A basic feature test example.
     */
    public function test_user_registration_with_valid_data(): void
    {
        
        $response = $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                "success" => true,
                "statusCode"=> 201,
                "message" => "User has been registered successfully.",
                "data" => [
                    "name"=> "user",
                    "email" => "user@gmail.com",
                    "token" => [
                        "expires_in" => 3600,
                        "access_token" => "fake-access-token",
                        "refresh_token"=> "fake-refresh-token"
                    ]
                ]
                
            ]);
        
        $this->assertDatabaseHas('users',[
            "name"=> "user",
            "email" => "user@gmail.com",
        ]);
    }

    /**
     * Register with duplicate email address
     */
    public function test_register_with_duplicate_email():void
    {
        /**Create new user */
        $this->postJson('/api/register', [
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        // Create second user with same email address.
        $response = $this->postJson('/api/register', [
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertJson([
            "message"=> "The email has already been taken.",
            "errors"=> [
                "email"=> [
                    "The email has already been taken."
                ]
            ]
        ]);
       
    }

    /**
     * Testing error messages - without email
     */
    public function test_registration_without_email():void
    {
        $response = $this->postJson('/api/register',[
            'email' => '',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertJson([
            "message"=> "The email field is required.",
            "errors"=> [
                "email"=> [
                    "The email field is required."
                ]
            ]
        ]);

    }

    /**
     * Testing error messages - without name
     */
    public function test_registration_without_name():void
    {
        $response = $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => '',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertJson([
            "message"=> "The name field is required.",
            "errors"=> [
                "name"=> [
                    "The name field is required."
                ]
            ]
        ]);
    }

    /**
     * Testing error messages - without password
     */
    public function test_registration_without_password():void
    {
        $response = $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertJson([
            "message"=> "The password field is required.",
            "errors"=> [
                "password"=> [
                    "The password field is required."
                ]
            ]
        ]);
    }

    /**
     * Testing error messages - unmatch passwords
     */
    public function test_registration_with_unmatch_passwords():void
    {
        $response = $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password_changed'
        ]);

        $response->assertJson([
            "message"=> "The password field confirmation does not match.",
            "errors"=> [
                "password"=> [
                    "The password field confirmation does not match."
                ]
            ]
        ]);
    }
}

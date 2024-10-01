<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthServerFailedTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Testing failed Auth2.0 server while registering
     */
    public function test_failing_auth_server_while_registering():void
    {
        // Mock the HTTP response for the OAuth token request
        Http::fake([
            env('APP_URL').'/oauth/token' => Http::response([
                'access_token' => 'fake-access-token',
                'refresh_token' => 'fake-refresh-token',
                'expires_in' => 3600,
            ], 400),
        ]);

        $response = $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertJson([
            'success' => false,
            'statusCode' => 500,
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseMissing('users',[
            "name"=> "user",
            "email" => "user@gmail.com",
        ]);

    }
}

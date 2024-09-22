<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test Password reset link can be requested
     */
    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * Testing password reset with valid token
     */
    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->postJson('/api/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertJson([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => 'Password reset successful'
                ])
                ->assertStatus(201);

            return true;
        });
    }

    /**
     * Testing password reset with Invalid token
     */
    public function test_password_can_be_reset_with_invalid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->postJson('/api/reset-password', [
                'token' => 'invalid token',
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertJson([
                    'success' => false,
                    'statusCode' => 400
                ])
                ->assertStatus(400);

            return true;
        });
    }
}

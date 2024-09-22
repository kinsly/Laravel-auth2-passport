<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
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
     * Test user mail can be verified with a valid verification link
     */
    public function test_email_can_be_verified():void
    {
        
        /**
         * Create new user
         */
        $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        //Get created user
        $user = User::where('email', 'user@gmail.com')->first();

        //Check about created user trigger registered event that is used to send mail verification
        //link. Use above fetched user to generate validation url send via a mail.
        Event::assertDispatched(Registered::class, function ($event) use (&$verificationUrl) {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => $event->user->id, 'hash' => sha1($event->user->email)]
            );
            return true;
        });
        //Mail verfication is not protected with bearer token in order to make mail verification easier
        $this->actingAs($user)->getJson($verificationUrl);
        //Check verified event is triggered
        Event::assertDispatched(Verified::class);
        //Check user mail verfication column is updated on DB
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

    }

    /**
     * Test mail verification fail on invalid verification links
     */
    public function test_email_is_not_verified_with_invalid_hash():void
    {
        
        /**
         * Create new user
         */
        $this->postJson('/api/register',[
            'email' => 'user@gmail.com',
            'name' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        //Get created user
        $user = User::where('email', 'user@gmail.com')->first();

        //Check about created user trigger registered event that is used to send mail verification
        //link. Use above fetched user to generate validation url send via a mail.
        Event::assertDispatched(Registered::class, function ($event) use (&$verificationUrl) {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => $event->user->id, 'hash' => sha1('invalid email')]
            );
            return true;
        });
        //Mail verfication is not protected with bearer token in order to make mail verification easier
        $this->actingAs($user)->getJson($verificationUrl);
        //Check verified event is not triggered
        Event::assertNotDispatched(Verified::class);
        //Check user mail verfication column is not updated on DB
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

    }
}

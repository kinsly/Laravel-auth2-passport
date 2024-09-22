<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

/** This url will be send as email verification link to use. It was set with route name verification.verify */
Route::get('/email/verify/{id}/{hash}',[VerifyEmailController::class, 'verifyEmail'] )
        ->middleware(['signed'])->name('verification.verify');

/** 
 * Password reset links 
**/

// Requesting password reset
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetRequest'])
        ->middleware('guest')->name('password.email');

//Resetting or updating new password
Route::post('/reset-password', [PasswordResetController::class, 'restPassword'])
        ->middleware('guest')->name('password.update');

/** Url of this route send to mail as a password reset link. 
 * Define this url on your front end application to grab token */
Route::get('/reset-password/{token}', function (string $token) {
    return [];
})->middleware('guest')->name('password.reset');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::group(['middleware' => ['auth:api', 'verified']], function () {
    Route::get('/profile', [AuthController::class, 'profile']);
});



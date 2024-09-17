<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

Route::get('/email/verify/{id}/{hash}',[ForgotPasswordController::class, 'verifyEmail'] )
        ->middleware(['auth:api', 'signed'])->name('verification.verify');


Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


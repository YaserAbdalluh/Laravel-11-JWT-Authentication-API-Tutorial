<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Middleware\TokenValidationMiddleware;
use App\Http\Controllers\API\FreelanceController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/codeVerified', [AuthController::class, 'verifyCode']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(TokenValidationMiddleware::class);
    Route::middleware('auth:api')->post('/profile', [AuthController::class, 'profile']);

    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware(TokenValidationMiddleware::class);
    Route::post('/profile', [AuthController::class, 'profile'])->middleware(TokenValidationMiddleware::class);
    Route::get('/verifyToken', [AuthController::class, 'verifyToken']);
    Route::get('/freelance-jobs', [FreelanceController::class, 'index']);

    // update profile and reset psw

    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);
    Route::post('/resetPassword', [AuthController::class, 'resetPassword']);
    Route::post('/resendOTP', [AuthController::class, 'resendOTP']);
    Route::post('/updateProfile', [AuthController::class, 'updateProfile']);
});

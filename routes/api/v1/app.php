<?php

use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\FavoriteController;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\ClientCroller;
use App\Http\Controllers\API\V1\MessageController;
use App\Http\Controllers\API\V1\ProfessionController;
use App\Http\Controllers\API\V1\WorkerCroller;
use App\Http\Middleware\TokenValidationMiddleware;

Route::middleware([
    'middleware' => 'api',
])->group(function () {


    // Category routes

    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    Route::post('/category', [CategoryController::class, 'store']);
    Route::post('/category/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/{id}', [CategoryController::class, 'destroy']);

    // Professions routes

    Route::get('/professions', [ProfessionController::class, 'index']);
    Route::get('/professions/{id}', [ProfessionController::class, 'show'])->middleware(TokenValidationMiddleware::class);
    ; // Get single
    Route::post('/professions', [ProfessionController::class, 'store']);
    Route::post('/professions/{id}', [ProfessionController::class, 'update']);
    Route::delete('/professions/{id}', [ProfessionController::class, 'destroy']);

    // Clients routes

    Route::get('/clients', [ClientCroller::class, 'index']);
    Route::get('/clients/{id}', [ClientCroller::class, 'show'])->middleware(TokenValidationMiddleware::class);
    ; // Get single
    Route::post('/clients', [ClientCroller::class, 'store']);
    Route::post('/clients/{id}', [ClientCroller::class, 'update']);
    Route::delete('/clients/{id}', [ClientCroller::class, 'destroy']);

    // Clients routes

    Route::get('/workers', [WorkerCroller::class, 'index']);
    Route::get('/workers/{id}', [WorkerCroller::class, 'show'])->middleware(TokenValidationMiddleware::class);
    ; // Get single
    Route::post('/workers', [WorkerCroller::class, 'store']);
    Route::post('/workers/{id}', [WorkerCroller::class, 'update']);
    Route::delete('/workers/{id}', [WorkerCroller::class, 'destroy']);

    // Favorites routes

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::get('/favorites/{id}', [FavoriteController::class, 'show'])->middleware(TokenValidationMiddleware::class);
    ; // Get single
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::post('/favorites/{id}', [FavoriteController::class, 'update']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

    // message broadcasting pusher


    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/messages/{sender_id}/{receiver_id}', [MessageController::class, 'getMessages']);

});
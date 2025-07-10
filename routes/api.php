<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TranslationController;
use App\Http\Controllers\API\LocaleController;
use App\Http\Controllers\API\TagController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('translations/export', [TranslationController::class, 'export']);
    Route::get('translations/search', [TranslationController::class, 'search']);
    Route::apiResource('translations', TranslationController::class);
    
    Route::apiResource('locales', LocaleController::class);
    Route::apiResource('tags', TagController::class);
});

Route::get('translations/export', [TranslationController::class, 'export']);
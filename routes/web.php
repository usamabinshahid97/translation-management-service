<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// API Documentation routes
Route::get('/api/documentation', [App\Http\Controllers\DocsController::class, 'index']);
Route::get('/api/docs.json', [App\Http\Controllers\DocsController::class, 'json']);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/start-oauth', [AuthController::class, 'startOauth'])->name('start-oauth');
// Route::post('/callback', [AuthController::class, 'oauthCallback'])->name('callback');
// Route::get('/get-token', [AuthController::class, 'getToken'])->name('get-token');
// Route::get('/get-write-key', [AuthController::class, 'getWriteKey']);


// Route::post('/get-keys', [AuthController::class, 'getKeys']);
// Route::get('/oauth/authorize', [AuthController::class, 'redirectToProvider']);
// Route::get('/auth/callback', [AuthController::class, 'handleProviderCallback']);
// Route::get('/get-token', [AuthController::class, 'getToken']);
// Route::post('/exchange-token', [AuthController::class, 'exchangeToken']);
// Route::get('/verify-token', [AuthController::class, 'verifyToken']);


Route::post('/start-oauth', [AuthController::class, 'startOauth'])->name('start-oauth');
Route::get('/auth/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/auth/callback', [AuthController::class, 'oauthCallback'])->name('callback');
Route::get('/get-token', [AuthController::class, 'getToken'])->name('get-token');
Route::get('/get-write-key', [AuthController::class, 'getWriteKey'])->name('get-write-key');
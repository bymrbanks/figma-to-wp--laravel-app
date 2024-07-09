<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/auth/redirect', [AuthController::class, 'customRedirectToProvider'])->name('auth.redirect');
Route::post('/auth/callback', [AuthController::class, 'customHandleProviderCallback'])->name('auth.callback');
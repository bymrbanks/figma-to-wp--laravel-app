<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Filament\Pages\CustomOAuthLogin;

Route::get('/projects', function () {
    return response()->json(['message' => 'Hello World!']);
});


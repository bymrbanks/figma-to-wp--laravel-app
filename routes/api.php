<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController; // Add this line to import the ProjectController class

Route::middleware('auth:sanctum')->post('/projects', [ProjectController::class, 'store']);

// Route::middleware('auth:sanctum')->get('/projects', [ProjectController::class, 'index']);

Route::middleware('validate_api_key')->get('/project/theme-json', [ProjectController::class, 'getThemeJson']);

Route::middleware('validate_api_key')->get('/project/patterns', [ProjectController::class, 'getPatterns']);
Route::middleware('validate_api_key')->get('/project/parts', [ProjectController::class, 'getParts']);
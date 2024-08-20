<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController; // Add this line to import the ProjectController class



// Get Routs
Route::middleware('validate_api_key')->get('/project/theme-json', [ProjectController::class, 'getThemeJson']);
Route::middleware('validate_api_key')->get('/project/theme-data', [ProjectController::class, 'getThemeData']);
Route::middleware('validate_api_key')->get('/project/templates', [ProjectController::class, 'getTemplates']);
Route::middleware('validate_api_key')->get('/project/patterns', [ProjectController::class, 'getPatterns']);
Route::middleware('validate_api_key')->get('/project/parts', [ProjectController::class, 'getParts']);
Route::middleware('validate_api_key')->get('/project/image/{filename}', [ProjectController::class, 'getImage']);


// Authenticated Post Routes
Route::middleware('auth:sanctum')->post('/project', [ProjectController::class, 'store']);
Route::middleware('auth:sanctum')->post('/project/image', [ProjectController::class, 'upload']);

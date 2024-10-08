<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController; // Add this line to import the ProjectController class
use App\Http\Controllers\ComponentController; // Add this line to import the ComponentController class



// Get Routs
Route::middleware('validate_api_key')->get('/project/theme-json', [ProjectController::class, 'getThemeJson']);
Route::middleware('validate_api_key')->get('/project/theme-data', [ProjectController::class, 'getThemeData']);
Route::middleware('validate_api_key')->get('/project/templates', [ProjectController::class, 'getTemplates']);
Route::middleware('validate_api_key')->get('/project/patterns', [ProjectController::class, 'getPatterns']);
Route::middleware('validate_api_key')->get('/project/parts', [ProjectController::class, 'getParts']);
// Route::middleware('validate_api_key')->get('/project/image/{filename}', [ProjectController::class, 'getImage']);
Route::middleware('validate_api_key')->get('/project/pages', [ProjectController::class, 'getPages']);
Route::middleware('validate_api_key')->post('/project/images', [ProjectController::class, 'getImages']);


Route::middleware('auth:sanctum')->post('/project/check-images', [ProjectController::class, 'checkImageExistence']);

// Authenticated Post Routes
Route::middleware('auth:sanctum')->post('/project', [ProjectController::class, 'store']);
Route::middleware('auth:sanctum')->post('/project/image', [ProjectController::class, 'upload']);

// Public API endpoints for Components
Route::get('/components', [ComponentController::class, 'index']);
Route::get('/components/{slug}', [ComponentController::class, 'show']);

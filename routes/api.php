<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController; // Add this line to import the ProjectController class

Route::middleware('auth:sanctum')->middleware('valid.token')->post('/projects', [ProjectController::class, 'store']);

// Route::middleware('auth:sanctum')->get('/projects', [ProjectController::class, 'index']);
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// <?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\ProjectController;

// Route::post('/projects', [ProjectController::class, 'store'])->middleware('auth.apikey');

Route::get('/projects', function () {
    return response()->json(['message' => 'Hello World!']);
});

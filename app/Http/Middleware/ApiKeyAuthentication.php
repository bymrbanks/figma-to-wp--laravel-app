<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiKeyAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');
        if (!$apiKey) {
            return response()->json(['message' => 'API key is required'], 401);
        }

        $user = User::where('api_key', $apiKey)->first();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->user = $user; // Add the user to the request
        return $next($request);
    }
}
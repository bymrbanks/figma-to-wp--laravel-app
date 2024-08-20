<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function startOauth(Request $request)
    {
        $writeKey = Str::random(40);
        $readKey = Str::random(40);

        // Store keys temporarily (e.g., in cache or database)
        Cache::put($writeKey, $readKey, 600); // 10 minutes

        return response()->json(['writeKey' => $writeKey]);
    }

    public function showLoginForm(Request $request)
    {
        $writeKey = $request->query('state');
        return view('auth.login', ['writeKey' => $writeKey]);
    }

    public function oauthCallback(Request $request)
    {
        $writeKey = $request->input('state');
        $readKey = Cache::get($writeKey);

        if ($readKey) {
            // Custom handling to get the token
            $token = $this->customHandleProviderCallback($request);

            // Store the access token securely (e.g., in cache or database)
            Cache::put($readKey, $token, 600); // 10 minutes
            
            return view('auth.oauth-success', ['readKey' => $readKey]);
        } else {
            return response('Invalid state parameter', 400);
        }
    }

    public function customHandleProviderCallback(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            // Generate a personal access token for the user
            $token = $user->createToken('YourAppName')->plainTextToken;

            return $token;
        } else {
            // Handle authentication failure
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    }

    public function getToken(Request $request)
    {
        $writeKey = $request->input('writeKey');
        $readKey = Cache::get($writeKey);
        $token = Cache::get($readKey);

        if ($token) {
            return response()->json(['token' => $token]);
        } else {
            return response()->json(['error' => 'Token not found'], 404);
        }
    }
}











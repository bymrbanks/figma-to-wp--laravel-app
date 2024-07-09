<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    public function customRedirectToProvider()
    {
        return view('auth.login');
    }

    public function customHandleProviderCallback(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            // Generate a personal access token for the user
            $token = $user->createToken('YourAppName')->plainTextToken;

            // Redirect with the access token
            $redirectUrl = $request->input('redirectUrl', 'https://www.default-redirect.com');
            return redirect()->to($redirectUrl . '?token=' . $token);
        } else {
            // Handle authentication failure
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    }
}
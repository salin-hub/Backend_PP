<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            // Retrieve user information from Google
            $googleUser = Socialite::driver('google')->user();

            // Search for an existing user by Google ID or email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            // If the user does not exist, create a new user record
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(16)), // Assign a secure random password
                ]);
            }

            // Generate a Sanctum token for the authenticated user
            $token = $user->createToken('LaravelSanctumAuth')->plainTextToken;

            // Return the user data and token as a JSON response
            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Google Authentication Error: ' . $e->getMessage());
            // Return an error response if authentication fails
            return response()->json(['error' => 'Failed to authenticate with Google', 'message' => $e->getMessage()], 500);
        }
    }
}

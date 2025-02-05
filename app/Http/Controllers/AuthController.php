<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate incoming registration data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|in:user,admin',
            'google_id' => 'sometimes|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
            ]);

            // Generate a token
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Generate a token for the user
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => $user->role,
                'user' => $user,
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
    public function loginadmin(Request $request)
{
    // Validate incoming request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();
    if ($user && $user->role === 'admin' && Hash::check($request->password, $user->password)) {
        $token = $user->createToken('adminToken')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    return response()->json(['error' => 'Unauthorized or not an admin'], 401);
}

    public function getAllAccounts(Request $request)
    {
        $users = User::where('role', '!=', 'admin')->get();

        if ($users->isNotEmpty()) {
            return response()->json([
                'message' => 'All non-admin user accounts retrieved successfully',
                'users' => $users,
            ], 200);
        }

        return response()->json(['error' => 'No non-admin users found'], 404);
    }

    // Get a single user account
    public function getAccount($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json(['user' => $user], 200);
        }

        return response()->json(['error' => 'User not found'], 404);
    }

    // Update user account
    public function updateAccount(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|string|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update user data
        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role ?? $user->role;
        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    // Delete user account
    public function deleteAccount($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // Logout the user
    public function logout(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            $user->tokens()->delete();
        } catch (Exception $e) {
            return response()->json(['error' => 'Could not log out user'], 500);
        }

        return response()->json(['message' => 'Logged out successfully!'], 200);
    }

    // Get specific authenticated account
    public function getSpecificAccount(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'google_id' => $user->google_id,
                ]
            ], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
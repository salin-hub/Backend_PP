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
        $credentials = $request->only('email', 'password');
    
        $user = User::where('email', $credentials['email'])->first();
    
        if (!$user || !$user->is_active) {
            return response()->json(['error' => 'Account is deactivated.'], 403);
        }
    
        if (Auth::attempt($credentials)) {
            // Login success
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['token' => $token, 'user' => $user]);
        }
    
        return response()->json(['error' => 'Invalid credentials'], 401);
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

        // Validate input including password confirmation
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'old_password' => 'required|string', // Old password required
            'password' => 'sometimes|required|string|min:8|confirmed', // checks for password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 400);
        }

        // Update name if provided
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ]
        ], 200);
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

    public function toggleStatus(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $user->is_active = $request->is_active;
        $user->save();

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'is_active' => $user->is_active
            ]
        ], 200);
    }


    public function logout(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            $user->tokens->each(function ($token) {
                $token->delete(); // Delete all tokens associated with the user
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not log out user: ' . $e->getMessage()], 500);
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required'
        ]);

        $created = User::create([
            'email' => $request->email,
            'password' => $request->password,
            'name' => $request->name,
            'role' => 'User'
        ]);

        if ($created) {
            return response()->json([
                'message' => 'Successfuly register!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'Server error'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }
        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out!'
        ], 200);
    }

    public function edit(Request $request)
    {
        $user = User::where('email', $request->user()->email)->value('name');
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['name' => $user]);
    }


    public function update(Request $request)
    {
        $user = User::where('email', $request->user()->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Successfully updated User'], 200);
    }
}

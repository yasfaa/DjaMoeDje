<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $created->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Akun telah berhasil dibuat'
        ], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }
        $user = User::where('email', $request['email'])->firstOrFail();

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Verifikasi email terlebih dahulu.'
            ], 403); 
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role,
            'name' => $user->name,
        ]);
    }

    function toogleBukaTutup(Request $request)
    {
        $auth = Auth::user();
        $user = User::find($auth->id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        $statusBaru = $user->remember_token === 'buka' ? 'tutup' : 'buka';

        $user->update([
            'remember_token' => $statusBaru,
        ]);

        return response()->json(['data' => $statusBaru], 200);
    }

    function toogleAdmin(Request $request)
    {
        $user = User::where('role', 'Admin')->first();

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        return response()->json(['data' => $user->remember_token], 200);
    }
    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil!'
        ], 200);
    }

    public function edit(Request $request)
    {
        $user = User::where('email', $request->user()->email)->value('name');
        if (!$user) {
            return response()->json(['error' => 'Pengguna tidak ditemukan'], 404);
        }

        return response()->json(['name' => $user]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
        ]);

        $auth = Auth::user();
        $user = User::where('id', $auth->id)->first();

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Berhasil memperbarui data pengguna'], 200);
    }

    public function updateAdmin(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required',
            'name' => 'required',
            'password' => 'nullable|string|min:6',
        ]);

        $auth = Auth::user();

        $user = User::where('id', $auth->id)->first();
        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        if ($request->filled('password') && Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password baru tidak boleh sama dengan password lama'], 400);
        }

        $user->name = $validate['name'];
        $user->email = $validate['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validate['password']);
        }

        $user->save();

        return response()->json(['message' => 'Berhasil memperbarui data pengguna'], 200);
    }
}
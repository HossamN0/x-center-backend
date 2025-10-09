<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required',
            'role' => ['sometimes', Rule::in(['admin', 'instructor', 'student'])],
        ]);

        $role = $validated['role'] ?? 'student';

        User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => $role === 'instructor' ? 'inactive' : 'active',
        ])->roles()->sync(Role::where('name', $role)->first());

        return response()->json([
            'message' => 'User registered successfully'
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $refresh_token = JWTAuth::claims([
            'token_type' => 'refresh',
        ])->fromUser(JWTAuth::user());

        $user = JWTAuth::user()->load('roles:id,name');
        $user->roles->makeHidden('pivot');
        // dd($userwithRole);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refresh_token,
        ], 200);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);
        try {
            $refresh_token = $request->refresh_token;
            $payload = JWTAuth::setToken($refresh_token)->getPayload();

            if ($payload->get('token_type') !== 'refresh') {
                return response()->json([
                    'message' => 'Invalid refresh token'
                ], 401);
            }

            $user = JWTAuth::setToken($refresh_token)->toUser();
            JWTAuth::invalidate($refresh_token);
            $access_token = JWTAuth::fromUser($user);
            $refresh_token = JWTAuth::claims([
                'token_type' => 'refresh',
            ])->fromUser($user);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'access_token' => $access_token,
                'refresh_token' => $refresh_token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }
}

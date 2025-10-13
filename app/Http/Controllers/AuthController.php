<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $role = $validated['role'] ?? 'student';
        unset($validated['role']);
        $validated['status'] = $role === 'instructor' ? 'inactive' : 'active';
        User::create($validated)
            ->roles()->sync(Role::where('name', $role)->first());

        return response()->json([
            'message' => 'User registered successfully'
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

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

    public function refresh(RefreshRequest $request)
    {
        $request->validated();
        try {
            $refresh_token = $request->refresh_token;
            $access_token = $request->access_token;
            $payload = JWTAuth::setToken($refresh_token)->getPayload();

            if ($payload->get('token_type') !== 'refresh') {
                return response()->json([
                    'message' => 'Invalid refresh token'
                ], 401);
            }

            $user = JWTAuth::setToken($refresh_token)->toUser();
            JWTAuth::invalidate($refresh_token);
            JWTAuth::invalidate($access_token);
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

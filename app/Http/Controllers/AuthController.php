<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $name = $request->first_name . ' ' . $request->last_name;

        $user = \App\Models\User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('token')->accessToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login a user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = auth()->user();

        $token = $user->createToken('token')->accessToken;

        return response([
            'token' => $token
        ]);
    }

    /**
     * Logout a user
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        auth()->user()->token()->revoke();

        return response([
            'message' => 'Logged out'
        ]);
    }
}

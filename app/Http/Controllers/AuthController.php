<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users',
            'password' => 'required|min:8|string|confirmed',
        ]);
        $user = User::create($fields);
        $token = $user->createToken($fields['name'])->plainTextToken;
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'string|required|exists:users',
            'password' => 'string|required|min:8'
        ]);
        $user = User::where('email', $fields['email'])->first();
        if (!$user | !Hash::check($fields['password'], $user->password)) {
            return [
                'message' => 'The Provided Credintials are incorrect !'
            ];
        }
        $token = $user->createToken($user->name)->plainTextToken;
        return [
            'user' => $user,
            'token' => $token
        ];

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return [
            'message' => "Successfuly Logged Out!"
        ];
    }
}

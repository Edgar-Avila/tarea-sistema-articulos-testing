<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create($data);
        $token = $user->createToken($data['email'])->plainTextToken;
        return ['token' => $token];
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if(!$user) {
            return response()->json(['success' => false], 401);
        }
        $passwordOk = Hash::check($data['password'], $user->password);
        if(!$passwordOk) {
            return response()->json(['success' => false], 401);
        }
        $token = $user->createToken($data['email'])->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true], 200);
    }
}

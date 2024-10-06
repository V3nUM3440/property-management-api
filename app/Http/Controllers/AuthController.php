<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function profile(Request $request): ProfileResource
    {
        return new ProfileResource($request->user());
    }

    public function login(Request $request): Response
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! Hash::check($data['password'], $user->password)) {
            return response([
                'message' => 'The provided credentials are incorrect'
            ], 401);
        }

        $token = $user->createToken($user->name)->plainTextToken;
        return response([
            'profile' => new ProfileResource($user),
            'token' => $token,
        ]);
    }

    public function verifyPassword(Request $request): Response
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        return response()->noContent();
    }

    public function changePassword(Request $request): Response
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        $request->user()->tokens()->delete();
        return response([
            'message' => 'You are logged out'
        ]);
    }
}

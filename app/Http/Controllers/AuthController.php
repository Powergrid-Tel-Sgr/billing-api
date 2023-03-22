<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return Response::json([
                'status' => 'error',
                'message' => 'Invalid Credentials'
            ], 400);
        }

        if (!Hash::check($request->password, $user->password)) {
            return Response::json([
                'status' => 'error',
                'message' => 'Invalid Credentials'
            ], 400);
        }

        if ($user) {
            return [
                'token' => $user->createToken('billing')->plainTextToken,
                'user' => new UserResource($user),
            ];
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return Response::json([
            'status' => 'success',
        ]);
    }
}

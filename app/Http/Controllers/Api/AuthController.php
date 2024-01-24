<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => 'No data provided'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'no_hp' => 'required|numeric',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        if (!Auth::attempt($request->only('no_hp', 'password'))) {
            return response()->json([
                'error' => 'Credentials not match'
            ], 401);
        }

        if (!Auth::attempt($validator->validated())) {
            return response()->json([
                'error' => 'Credentials not match'
            ], 401);
        }

        $user = User::where('no_hp', $request->no_hp)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status_verifikasi' => $user->isVerified,
            'user' => $user
        ], 200);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'message' => 'Tokens Revoked'
        ], 200);
    }

    public function getLoginUser()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }
            $user = User::where('id', Auth::user()->id)
            ->with('posProfile')
            // ->select('id', 'name', 'email', 'no_hp', 'role_id', 'isVerified')
            ->firstOrFail();

            return response()->json([
                'user' => $user
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}

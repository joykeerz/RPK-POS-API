<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosProfile;
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
        if ($user->role_id != 5) {
            return response()->json([
                'error' => 'user is not customer'
            ], 401);
        }

        $checkProfile = PosProfile::where('user_id', $user->id)->first();
        $isFirstTime = false;
        if ($checkProfile == null || !$checkProfile) {
            $isFirstTime = true;
            $profile = new PosProfile();
            $profile->pos_name = $user->name;
            $profile->user_id = $user->id;
            $profile->pin = '000000';
            $profile->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status_verifikasi' => $user->isVerified,
            'user' => $user,
            'isFirstTime' => $isFirstTime
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

    public function getProfileUser()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }
            $profile = PosProfile::where('user_id', Auth::user()->id)->firstOrFail();
            return response()->json([
                'profile' => $profile
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function isFirstTimeLogin()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }
            $profile = PosProfile::where('user_id', Auth::user()->id)->firstOrFail();
            if ($profile->pin == '000000') {
                return response()->json([
                    'isFirstTime' => true
                ], 200);
            }
            return response()->json([
                'isFirstTime' => false
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => 'No data provided'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'pos_name' => 'required|string|min:3|max:255',
            'pin' => 'required|string|min:6|max:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $profile = PosProfile::where('user_id', Auth::user()->id)->first();
        if ($profile == null || !$profile) {
            return response()->json([
                'error' => 'Profile not found'
            ], 404);
        }

        $profile->pos_name = $request->pos_name;
        $profile->pin = Hash::make($request->pin);
        $profile->save();

        return response()->json([
            'message' => 'Profile updated'
        ], 200);
    }

    public function updateUserPin(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => 'No pin provided'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|min:6|max:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $profile = PosProfile::where('user_id', Auth::user()->id)->first();

        if ($profile == null || !$profile) {
            return response()->json([
                'error' => 'Profile not found'
            ], 404);
        }

        $profile->pin = Hash::make($request->pin);
        $profile->save();

        return response()->json([
            'message' => 'Pin updated'
        ], 200);
    }
}

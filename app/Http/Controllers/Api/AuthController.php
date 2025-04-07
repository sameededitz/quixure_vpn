<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'normal'
        ]);

        /** @var \App\Models\User $user **/
        $user->assignFreeTrial();

        SendEmailVerification::dispatch($user)->delay(now()->addSeconds(2));

        return response()->json([
            'status' => true,
            'message' => 'Account created successfully! Verify your Email',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 400);
        }

        $user = User::where('email', $request->name)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => "We couldn't find an account with that email."
            ], 400);
        }

        $credentials = [
            'email' => $request->name,
            'password' => $request->password,
        ];
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please verify your email address.',
                    'user' => $user
                ], 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'The provided credentials do not match our records.'
        ], 400);
    }

    public function logout()
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully!'
        ], 200);
    }
}

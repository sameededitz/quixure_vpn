<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use App\Services\AppleToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    public function handleGoogleCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 400);
        }

        $accessToken = $request->input('token');
        try {
            /** @disregard @phpstan-ignore-line */
            $googleUser = Socialite::driver('google')->userFromToken($accessToken);

            Log::channel('auth')->info('Google user retrieved: ', ['user' => $googleUser]);
            
            // Check if the user already exists
            /** @var \App\Models\User $user **/
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If user exists, update all details except email
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                /** @var \App\Models\User $user **/
                // If user does not exist, create a new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(10)),
                    'email_verified_at' => now(),
                ]);

                $user->assignFreeTrial();
            }

            // Log the user in
            Auth::login($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            Log::channel('auth')->error('Error logging in with Google Api: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error logging in with Google: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function handleAppleCallback(Request $request, AppleToken $appleToken)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 400);
        }

        $id_token = $request->input('id_token');

        try {
            config()->set('services.apple.client_secret', $appleToken->generateClientSecret(true));

            /** @disregard @phpstan-ignore-line */
            $appleUser = Socialite::driver('apple')->userFromToken($id_token);

            Log::channel('auth')->info('Apple user retrieved: ', ['user' => $appleUser]);

            if (!$appleUser->user['email_verified']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your email address is not verified. Please verify your Apple account.',
                ], 400);
            }

            // Extract user details
            $appleId = $appleUser->id;
            $email = $appleUser->email;
            $name = $appleUser->name ?? $email;

            /** @var \App\Models\User $user **/
            $user = User::where('apple_id', $appleId)->orWhere('email', $email)->first();

            if ($user) {
                // Update Apple ID if needed
                $user->update(['apple_id' => $appleId]);
            } else {
                /** @var \App\Models\User $user **/
                // Create a new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'apple_id' => $appleId,
                    'password' => Hash::make(Str::random(10)),
                    'email_verified_at' => now(),
                ]);

                $user->assignFreeTrial();
            }

            // Log the user in
            Auth::login($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            Log::channel('auth')->error('Error logging in with Apple Api: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error logging in with Apple: ' . $e->getMessage(),
            ], 500);
        }
    }
}

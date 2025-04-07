<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyController extends Controller
{
    public function verify(Request $request)
    {
        $user = Auth::user() ? Auth::user() : User::findOrFail($request->route('id'));
        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        if ($user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? response()->json([
                    'status' => true,
                    'message' => 'Email already Verified'
                ], 200)
                : redirect()->route('home')->with('verified', true);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $request->wantsJson()
            ? response()->json([
                'status' => true,
                'message' => 'Email verified successfully!'
            ], 200)
            : view('auth.verified-email');
    }

    public function viewEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if (hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return view('email.custom-email-verfication', [
                'user' => $user,
                'verificationUrl' => URL::temporarySignedRoute(
                    'verification.verify',
                    Carbon::now()->addMinutes(Config::get('auth.passwords.users.expire', 60)),
                    [
                        'id' => $user->getKey(),
                        'hash' => sha1($user->getEmailForVerification()),
                    ]
                ),
                'viewInBrowserUrl' => null,
            ]);
        }
        abort(403);
    }

    public function viewInBrowser($email, $token)
    {
        $user = User::where('email', $email)->first();

        return view('email.custom-password-reset', [
            'user' => $user,
            'resetUrl' => route('password.reset', ['token' => $token, 'email' => $email]),
            'viewInBrowserUrl' => null,
        ]);
    }
}

<?php

namespace App\Livewire\Actions;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmail
{
    /**
     * Handle the email verification process.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user() ?? User::findOrFail($request->route('id'));

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        if ($user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? response()->json([
                    'status' => true,
                    'message' => 'Email already Verified',
                ])
                : view('auth.verified-email');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $request->wantsJson()
            ? response()->json([
                'status' => true,
                'message' => 'Email verified successfully!',
            ])
            : view('auth.verified-email');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return $user->hasPrivilegedRole()
                ? redirect()->intended(route('dashboard', ['verified' => 1], absolute: false))
                : redirect()->to(route('home', ['verified' => 1], absolute: false));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $user->hasPrivilegedRole()
            ? redirect()->intended(route('dashboard', ['verified' => 1], absolute: false))
            : redirect()->to(route('home', ['verified' => 1], absolute: false));
    }
}

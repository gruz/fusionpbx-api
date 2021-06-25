<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserWasActivated;
use Illuminate\Auth\Events\Verified;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\AbstractController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends AbstractController
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
            event(new UserWasActivated($request->user()));
        }

        return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
    }
}

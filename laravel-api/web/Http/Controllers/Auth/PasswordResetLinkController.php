<?php

namespace Web\Http\Controllers\Auth;

use Api\Domain\Services\DomainService;
use Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create(DomainService $domainService)
    {
        $domains = $domainService->getDomainsArray();
        return view('auth.forgot-password', ['domains' => $domains]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain_uuid' => 'required|uuid',
            'user_email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $data = $request->only([
            'domain_uuid',
            'user_email'
        ]);
        $status = Password::sendResetLink(
            $data
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($data)
                            ->withErrors(['email' => __($status)]);
    }
}

<?php

namespace Web\Http\Controllers\Auth;

use Api\User\Services\UserService;
use Web\Http\Controllers\Controller;
use Api\Domain\Services\DomainService;
use Illuminate\Support\Facades\Password;
use Infrastructure\Auth\Requests\UserForgotPasswordRequest;

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
        $domains = array_combine(array_values($domains),$domains);
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
    public function store(UserForgotPasswordRequest $request, UserService $userService)
    {
        // $validated = $request->validate([
        //     'domain_uuid' => 'required|uuid',
        //     'user_email' => 'required|email',
        // ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        // $data = $request->only([
        //     'domain_uuid',
        //     'user_email'
        // ]);
        $data = $request->validated();

        $user = $userService->getUserByEmailAndDomain($data['user_email'], $data['domain_name']);
        $data['username'] = $user->username;

        $status = Password::sendResetLink(
            $data
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($data)
                            ->withErrors(['email' => __($status)]);
    }
}

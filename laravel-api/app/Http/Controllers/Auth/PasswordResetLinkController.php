<?php

namespace App\Http\Controllers\Auth;

use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Services\DomainService;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Auth\UserForgotPasswordRequestWeb;

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
    public function store(UserForgotPasswordRequestWeb $request, UserService $userService)
    {
        $data = $request->validated();

        $user = $userService->getUserByEmailAndDomain($data['user_email'], $data['domain_name']);
        $data['username'] = $user->username;
        $data['domain_uuid'] = $user->domain_uuid;
        unset($data['domain_name']);

        $status = Password::sendResetLink(
            $data
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($data)
                            ->withErrors(['email' => __($status)]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Requests\LoginRequest;
use App\Services\Fpbx\DomainService;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\AbstractController;

class AuthenticatedSessionController extends AbstractController
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create(DomainService $domainService)
    {
        $domains = $domainService->getDomainsArray();
        return view('auth.login', ['domains' => $domains]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Requests\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

<?php

namespace Web\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Api\Domain\Models\Domain;
use Api\Domain\Services\DomainService;
use Illuminate\Support\Facades\Auth;
use Web\Http\Controllers\Controller;
use Web\Http\Requests\Auth\LoginRequest;
use Infrastructure\Providers\RouteServiceProvider;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create(DomainService $domainService)
    {
        $domains = Domain::where('domain_enabled', true)->get()->pluck('domain_name', 'domain_uuid')->toArray();
        return view('auth.login', ['domains' => $domains]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Web\Http\Requests\Auth\LoginRequest  $request
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

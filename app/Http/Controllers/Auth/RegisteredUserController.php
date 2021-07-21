<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Services\Fpbx\DomainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Requests\UserSignupRequestWeb;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\AbstractController;

class RegisteredUserController extends AbstractController
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create(DomainService $domainService)
    {
        $domains = $domainService->getDomainsArray();
        return view('auth.register', ['domains' => $domains]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(UserSignupRequestWeb $request)
    {
        $validated = $request->validated();
        $validated['username'] = $validated['user_email'];

        $request = \Illuminate\Support\Facades\Request::create(route('fpbx.user.signup'), 'POST');
        \Request::replace($validated);
        $request->headers->set('X-apitoken', config('fpbx.api_token'));
        $response = Route::dispatch($request);

        // $statusCode = $response->getStatusCode();

        // if (201 === $statusCode) {
        //     return view('auth.confirmation_sent');
        // }
        $json = $response->getContent();
        $content = json_decode($json);

        $user = User::where('user_uuid', $content->user_uuid)->first();

        // dd($json, $content, $user, $user->extensions->pluck('extension'));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

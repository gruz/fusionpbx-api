<?php

namespace Web\Http\Controllers\Auth;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Support\Facades\Auth;
use Web\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Web\Http\Requests\Auth\UserSignupRequest;
use Infrastructure\Providers\RouteServiceProvider;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $domains = Domain::where('domain_enabled', true)->get()->pluck('domain_name', 'domain_uuid')->toArray();
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
    public function store(UserSignupRequest $request)
    {
        $validated = $request->validated();
        // dd($validated);
        // $domain_name = Domain::where('domain_uuid', $validated['domain_uuid'])

        $request = \Illuminate\Support\Facades\Request::create(route('fpbx.user.signup'), 'POST');
        \Request::replace($validated);
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

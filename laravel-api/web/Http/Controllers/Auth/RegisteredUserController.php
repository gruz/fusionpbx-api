<?php

namespace Web\Http\Controllers\Auth;

use Api\User\Models\User;
use Api\Domain\Services\DomainService;
use Illuminate\Support\Facades\Auth;
use Web\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;
use Web\Http\Requests\Auth\UserSignupRequestWeb;

class RegisteredUserController extends Controller
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
        $validated['username'] = $request->get('user_email');
        // dd($validated, $request->get('extensions'));
        // $domain_name = Domain::where('domain_uuid', $validated['domain_uuid'])

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

<?php

namespace App\Http\Controllers\Auth;

use App\Services\Fpbx\DomainService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Services\ValidationRulesService;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, ValidationRulesService $validationRulesService, DomainService $domainService)
    {
        $password_rules = $validationRulesService->getPasswordRules('user');
        $password_rules[] = 'confirmed';
        $request->validate([
            'token' => 'required',
            'user_email' => 'required|email',
            'password' => $password_rules,
        ]);

        $data = $request->only('user_email', 'password', 'password_confirmation', 'token');
        $domain_name = $request->get('domain_name');

        $domainModel = $domainService->getByAttributes(['domain_name' => $domain_name, 'domain_enabled' => true])->first();
        $data['domain_uuid'] = optional($domainModel)->domain_uuid;

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $data,
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    // 'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}

<?php

namespace Web\Http\Requests\Auth;

use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'domain_uuid' => 'required|string|uuid',
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $data = $this->only('domain_uuid', 'username', 'password');
        // $data['user_enabled'] = 'true';
        // $data['domain_uuid'] = 'ff1f7bf7-9dcb-46a1-ad77-ff22e0a08f26';
        // $data['username'] = 'admin';
        // $data['password'] = 'admin';

        if (! Auth::attempt($data, $this->filled('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // $user = User::where([
            //     ['domain_uuid' ,$data['domain_uuid']],
            //     ['username' ,$data['username']],
            // ])->first();
            // if (!empty($user) && $user->user_enabled !== 'true') {
            //     throw ValidationException::withMessages([
            //         'user_enabled' => __('auth.verify'),
            //     ]);
            // }
            // dd($data, $user, $user->user_enabled);
    

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}

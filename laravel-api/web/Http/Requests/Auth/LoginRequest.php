<?php

namespace Web\Http\Requests\Auth;

use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Api\Domain\Services\DomainService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }


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
            'domain_name' => 'nullable|string',
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

        $domain_name = $this->get('domain_name');
        if (empty($domain_name)) {
            $domainModel = $this->domainService->getSystemDomain();
            // $domain_name = $domainModel->getAttribute('domain_name');
        } else {
            $domainModel = $this->domainService->getByAttributes(['domain_name' => $domain_name, 'domain_enabled' => true], ['limit' => 1])->first();
        }
        $domain_uuid = optional($domainModel)->getAttribute('domain_uuid');
        // dd($domain_name, $domain_uuid);
        $data = $this->only('username', 'password');
        $data['domain_uuid'] = $domain_uuid;
        // $data['user_enabled'] = 'true';
        // $data['domain_uuid'] = 'ff1f7bf7-9dcb-46a1-ad77-ff22e0a08f26';
        // $data['username'] = 'admin';
        // $data['password'] = 'admin';

        if (! Auth::attempt($data, $this->filled('remember'))) {
            RateLimiter::hit($this->throttleKey());

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

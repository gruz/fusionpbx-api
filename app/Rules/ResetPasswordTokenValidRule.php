<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ResetPasswordTokenValidRule implements Rule
{
    private $email;

    private $domain_name;

    private $token;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email, $domain_name, $token)
    {
        //
        $this->email = $email;
        $this->domain_name = $domain_name;
        $this->token = $token;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // $userModel = $this->userService->getUserByEmailAndDomain(request()->email, request()->domain_name);
        // $databaseTokenRepository = app(DatabaseTokenRepository::class);
        // $tokenExists = $databaseTokenRepository->exists($userModel, request()->token);
        // dd($userModel, $tokenExists);
        $hasher = app(\Illuminate\Contracts\Hashing\Hasher::class);
        $resetRecord = \DB::table(('password_resets'))->where([
            ['email', $this->email],
            ['domain_name', $this->domain_name],
        ])->first();

        if (empty($resetRecord)) {
            return false;
        }

        $expires = config('auth.passwords.users.expire') * 60;
        $expired = \Carbon\Carbon::parse($resetRecord->created_at)->addSeconds($expires)->isPast();
        $tokenExists = $hasher->check($this->token, $resetRecord->token);
        return !$expired && $tokenExists;
        // dd(request()->token, $resetRecord->token, $expired, $tokenExists);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Invalid token');
    }
}

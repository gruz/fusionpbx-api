<?php

namespace Gruz\FPBX\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PasswordReset extends Model
{
    protected $primaryKey = 'token';

    public $timestamps = true;

    const UPDATED_AT = null;

    /**
     * If current token is expired throws an excetpion
     *
     * @return void
     * @throws BindingResolutionException
     * @throws UnprocessableEntityHttpException
     */
    public function checkExpired()
    {
        $countTime = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
        $expireDate = $this->created_at->addMinutes($countTime);

        $now = \Carbon\Carbon::now();

        if ($now > $expireDate) {
            throw new  UnprocessableEntityHttpException(__('Password reset request expired'));
        }
    }
}

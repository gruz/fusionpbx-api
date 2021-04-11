<?php

namespace Api\User;

use Illuminate\Auth\Notifications\ResetPassword;
use Api\User\Password\PasswordBrokerManager;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider as OriginalPasswordResetServiceProvider;

class PasswordResetServiceProvider extends OriginalPasswordResetServiceProvider
{
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerPolicies();

        /**
         * @var User $user
         */
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->getEmailForPasswordReset(),
                    // 'domain_name' => $user->getDomainNameForPasswordReset()
                    'domain_name' => $user->domain->domain_name,
                ], false));
        });

    }
}
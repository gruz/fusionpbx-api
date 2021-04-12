<?php

namespace Api\User\Repositories;

use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Carbon;
use Api\User\Models\User;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as OriginalDatabaseTokenRepository;

class DatabaseTokenRepository extends OriginalDatabaseTokenRepository
{

    /**
     * Domain name to which user belongs. Due to implementation of interface
     * some methods cannot be changed. We use this class variable to set user`s domain name
     * and use it when storing data to "password_resets" table.
     */
    private $domainName;

    /**
     * Create a new token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {
        $email = $user->getEmailForPasswordReset();
        $this->domainName  = $this->getUserDomainName($user);
        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($email, $token));

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return int
     */
    protected function deleteExisting(CanResetPasswordContract $user)
    {
        return $this->getTable()
            ->where([
                'email' => $user->getEmailForPasswordReset(),
                'domain_name' =>  $this->domainName ?
                    $this->domainName :
                    $this->getUserDomainName($user)
            ])
            ->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  string  $email
     * @param  string  $token
     * @return array
     */
    protected function getPayload($email, $token)
    {
        return [
            'email' => $email,
            'token' => $this->hasher->make($token),
            'domain_name' => $this->domainName,
            'created_at' => new Carbon
        ];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        $record = (array) $this->getTable()
            ->where([
                'email' => $user->getEmailForPasswordReset(),
                'domain_name' =>  $this->domainName ?
                    $this->domainName :
                    $this->getUserDomainName($user)
            ])
            ->first();

        return $record &&
            !$this->tokenExpired($record['created_at']) &&
            $this->hasher->check($token, $record['token']);
    }

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return bool
     */
    public function recentlyCreatedToken(CanResetPasswordContract $user)
    {
        $record = (array) $this->getTable()
            ->where([
                'email' => $user->getEmailForPasswordReset(),
                'domain_name' =>  $this->domainName ?
                    $this->domainName :
                    $this->getUserDomainName($user)
            ])
            ->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    /**
     * Function to get users domain name to which he belongs.
     *
     * @param CanResetPasswordContract $user
     * @return string Users domain name
     */
    protected function getUserDomainName(CanResetPasswordContract $user)
    {
        // return User::find($user->user_uuid)->getDomainNameForPasswordReset();
        return $user->domain->getAttribute('domain_name');
    }
}

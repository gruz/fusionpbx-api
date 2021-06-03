<?php

namespace Infrastructure\Auth\Notifications;

use config;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends \Illuminate\Auth\Notifications\ResetPassword
{
    /**
     * @var \Api\User\Models\User
     */
    public $user;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token, $user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        $mailMessage = parent::buildMailMessage($url);
        if (config('fpbx.user.include_username_in_reset_password_email')) {
            $mailMessage->line(Lang::get('Username') . ': **' . $this->user->username . '**');
        }
        return $mailMessage;
    }
}

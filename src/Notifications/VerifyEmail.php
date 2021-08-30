<?php

namespace Gruz\FPBX\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationCode = $this->verificationCode($notifiable);
        $verificationUrl = $this->verificationUrl($notifiable, $verificationCode);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return $this->buildMailMessage($verificationUrl, $verificationCode);
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url, $code)
    {
        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->line(__('Use validation code **:code**', ['code' => $code]))
            ->line(__('or press the button below'))
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable, $verificationCode)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return $url = route('fpbx.user.activate', [
            'user_uuid' => $notifiable->getKey(),
            'user_enabled' => $verificationCode
        ]);

        // return URL::temporarySignedRoute(
        //     'fpbx.user.activate',
        //     Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
        //     [
        //         'id' => $notifiable->getKey(),
        //         'hash' => sha1($notifiable->getEmailForVerification()),
        //     ]
        // );
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationCode($notifiable)
    {
        $value = $notifiable->getAttribute('user_enabled');
        $value = explode('::', $value);
        return $value[0];
    }

    /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}

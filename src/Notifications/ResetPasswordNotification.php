<?php

namespace Gruz\FPBX\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \Gruz\FPBX\Models\User
     */
    public $user;
    public $token;

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
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            ->line(__('Domain') . ': **' . $this->user->domain_name . '**')
            ->line(__('Username') . ': **' . $this->user->username . '**')
            ->line(__('Use validation code **:code**', ['code' => $this->token]))
            // ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('This password reset code expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));

            // ->greeting(__('Domain signup verification'))
            // ->line(__('To finish your domain **:domain_name** registration process we must verify your email.', ['domain_name' => $this->model->request['request']['domain_name']]))
            // ->line(__('Use validation code **:code**', ['code' => $this->model->code]))
            // ->line(__('or press the button below'))
            // ->action(__('Verify your email'), $url)
            // ->line(__('Thank you for using our service!'))
            // ;

        if (config('fpbx.user.include_username_in_reset_password_email')) {
            $mailMessage->line(Lang::get('Username') . ': **' . $this->user->username . '**');
        }


        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * // ##mygruz20210313160640 We don't use this method, but I place
     * it here JIC. Not sure if it's a must
     * @link https://laravel.com/docs/8.x/notifications#formatting-database-notifications
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->model->toArray();
    }
}

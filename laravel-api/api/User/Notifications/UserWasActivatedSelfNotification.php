<?php

namespace Api\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserWasActivatedSelfNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $model;

    private $username;

    private $password;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
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

        $domain_uuid = $this->model->domain->getAttribute('domain_uuid');
        $domain_name = $this->model->domain->getAttribute('domain_name');

        $extensions = $this->model->extensions->pluck('password', 'extension');

        $mailMessage =  (new MailMessage)
            ->subject(__('Your email was verified'))
            ->greeting(__('Your account has been activated'))
            ->line(__('Your domain **:domain_name**', [
                'domain_name' => $domain_name
            ]));

            if ($extensions->count() > 0) {
                $mailMessage->line( '# '. __('You voice account(s) to login with the app'));
                foreach ($extensions as $extension => $password) {
                    $mailMessage->line( ' * ' . __('Login') . ': **' . $extension . '** ');
                }
                $mailMessage->line( __('Use the password you have specified on registration'));
            }

            $mailMessage->line( '# ' . __('Web portal'));
            $mailMessage->line(__('Use username **:username** to login into web-application', ['username' => $this->model->username]));
            $mailMessage->salutation(__('Thank you for using our service!'));

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

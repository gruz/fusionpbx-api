<?php

namespace Api\Domain\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DomainActivateActivatorNotification extends Notification implements ShouldQueue
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
    public function __construct($model, $username, $password)
    {
        $this->model = $model;
        $this->username = $username;
        $this->password = $password;
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
        return (new MailMessage)
            ->subject(__('Your domain has been activated'))
            ->greeting(__('Your domain has been activated'))
            ->line(__('Your domain **:domain_name** was activated', [
                'domain_name' => $this->model->getAttribute('domain_name')
            ]))
            ->line(__('Domain UUID is:'))
            ->line(__('**:domain_uuid**', [
                'domain_uuid' => $this->model->domain_uuid,
            ]))
            ->line(__('Use credentials to login:'))
            ->line(__('**:username**', [ 'username' => $this->username, ]))
            ->line(__('**:password**', [ 'password' => $this->password, ]))
            // ->action(__('Verify your email'), $url)
            // ->line(__('Thank you for using our service!'))
            ;
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

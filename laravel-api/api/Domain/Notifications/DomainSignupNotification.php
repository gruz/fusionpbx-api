<?php

namespace Api\Domain\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DomainSignupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $model;

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
        $address = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable
            ? $notifiable->routeNotificationFor('mail')
            : $notifiable->email;

        // $notifiable->mail
        $url = route('fpbx.get.domain.activate', [
            'hash' => $this->model->hash,
            'email' => $address,
        ]);

        return (new MailMessage)
            ->greeting(__('Domain signup verification'))
            ->line(__('To finish your domain **:domain_name** registration process we must verify your email.', ['domain_name' => $this->model->request['domain_name']]))
            ->action(__('Verify your email'), $url)
            ->line(__('Thank you for using our service!'))
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

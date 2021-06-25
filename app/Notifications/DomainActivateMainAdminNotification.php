<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DomainActivateMainAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $model;
    private $activatorEmail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($model, $activatorEmail)
    {
        $this->model = $model;
        $this->activatorEmail = $activatorEmail;
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
            ->subject(__('Alert! New domain registered at ' . config('app.name')))
            ->greeting(__('New domain registered'))
            ->line(__('New domain **:domain_name** was registered by user **:activator_email**', [
                'domain_name' => $this->model->getAttribute('domain_name'),
                'activator_email' => $this->activatorEmail,
            ]))
            ->line(__('Domain UUID is:'))
            ->line(__('**:domain_uuid**', [
                'domain_uuid' => $this->model->domain_uuid,
            ]))
            // ->action(__('Verify your email'), $url)
            ->line(__('You are getting this message because you are :link admin user', [
                'link' => '['. config('app.name') . '](' . \Request::root() . ')'
            ] ))
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

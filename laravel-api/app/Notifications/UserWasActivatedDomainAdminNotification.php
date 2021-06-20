<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserWasActivatedDomainAdminNotification extends Notification implements ShouldQueue
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
        $domain_uuid = $this->model->domain->getAttribute('domain_uuid');
        $domain_name = $this->model->domain->getAttribute('domain_name');

        return (new MailMessage)
            ->subject(__('Alert! New user was activated at :app_name', ['app_name' => config('app.name')]))
            ->greeting(__('A new user registered in domain **:domain_name**', ['domain_name' => $domain_name ]))
            ->line(__('Username') . ': **' . $this->model->username . '**')
            ->line(__('Email') . ': **' . $this->model->user_email . '**')
            ->line(__('Reseller code') . ': **' . $this->model->reseller_code . '**');
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

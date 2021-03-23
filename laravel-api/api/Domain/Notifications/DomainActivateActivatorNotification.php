<?php

namespace Api\Domain\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DomainActivateActivatorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $model;

    private $userData;



    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($model, $userData)
    {
        $this->model = $model;
        $this->userData = $userData;
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
        /**
         * @var MailMessage
         */
        $mail =  (new MailMessage)
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
            ->line(__('**:username**', [ 'username' => $this->userData['username'], ]))
            ->line(__('**:password**', [ 'password' => $this->userData['password'], ]))
            // ->action(__('Verify your email'), $url)
            // ->line(__('Thank you for using our service!'))
            ;

        $mail->line(__('## Extensions:'));

        $activatorExtensions = collect(Arr::get($this->userData, 'extensions'))->pluck('password','extension')->toArray();
        foreach ($activatorExtensions as $extension => $password) {
            $mail->line(__('- Number: **:username**', [ 'username' => $extension, ]))
            ->line(__('- Password: :password', [ 'password' => $password, ]))
            ->line('===')
            ->line('');
        }

        return $mail;
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

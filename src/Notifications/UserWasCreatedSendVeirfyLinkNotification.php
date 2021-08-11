<?php

namespace Gruz\FPBX\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserWasCreatedSendVeirfyLinkNotification extends Notification implements ShouldQueue
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
        // $this->model->loadMissing('domain');
        $domain_uuid = $this->model->domain->getAttribute('domain_uuid');
        $domain_name = $this->model->domain->getAttribute('domain_name');
        $activationUrl = \Request::root() . '/user/activate/' . $this->model->getAttribute('user_enabled');

        return (new MailMessage)
            ->subject(__('Please confirm your email for :app_name | :url',[
                'app_name' => config('app.name'),
                'url' => \Request::root(),
            ] ))
            ->greeting(__('Your email was registered to :link',[
                'link' => '['. config('app.name') . '](' . \Request::root() . ')'
            ]))
            ->line(__('Your domain **:domain_name**', [
                'domain_name' => $domain_name
            ]))
            // ->line(__('Domain UUID is:'))
            // ->line(__('**:domain_uuid**', [
            //     'domain_uuid' => $domain_uuid,
            // ]))
            ->action('Activate your account', $activationUrl)
            ->salutation(__('Thank you for using our service!'))
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

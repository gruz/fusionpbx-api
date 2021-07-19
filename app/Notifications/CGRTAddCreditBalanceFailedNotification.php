<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CGRTAddCreditBalanceFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $options;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $options = [])
    {
        $this->user = $user;
        $this->options = $options;
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
        $payment_info = \Str::markdown('```' . PHP_EOL . print_r($this->options, true) . PHP_EOL . '```');
        return (new MailMessage)
            ->error()
            ->subject(__('Alert! CGRT Add credit balance error '))
            ->greeting(__('Payment successfully recieved but CGRT balance could not be updated'))
            ->line(__('Username') . ': ')
            ->line($this->user->username)
            ->line(__('Domain') . ': ')
            ->line($this->user->domain_name)
            ->line(__('Payment info') . ': ')
            ->line(new HtmlString($payment_info))
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

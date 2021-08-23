<?php

namespace Gruz\FPBX\Notifications;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CGRTFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
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
        $userData = Str::markdown('```' . PHP_EOL . print_r($this->event->userData, true) . PHP_EOL . '```');
        $request = Str::markdown('```' . PHP_EOL . print_r($this->event->request, true) . PHP_EOL . '```');
        $response = Str::markdown('```' . PHP_EOL . print_r($this->event->response, true) . PHP_EOL . '```');
        return (new MailMessage)
            ->error()
            ->subject(__('Alert! CGRT Error '))
            ->greeting(__('A CGRT API communication error'))
            ->line(__('Request') . ': ')
            ->line(new HtmlString($request))
            ->line(__('Response') . ': ')
            ->line(new HtmlString($response))
            ->line(__('User info') . ': ')
            ->line(new HtmlString($userData));
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

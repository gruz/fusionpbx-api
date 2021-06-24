<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FPBXFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;

    private $cmd;

    private $output;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message, $cmd, $output)
    {
        $this->message = $message;
        $this->cmd = $cmd;
        $this->output = $output;
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
        $message = \Str::markdown('```' . PHP_EOL . print_r($this->message, true) . PHP_EOL . '```');
        $cmd = \Str::markdown('```' . PHP_EOL . print_r($this->cmd, true) . PHP_EOL . '```');
        $output = \Str::markdown('```' . PHP_EOL . print_r($this->output, true) . PHP_EOL . '```');
        return (new MailMessage)
            ->error()
            ->subject(__('Alert! FPBX Hook Error'))
            ->greeting(__('Could not run FPBX Hook'))
            ->line(__('Error') . ': ')
            ->line(new HtmlString($message))
            ->line(__('Hook command') . ': ')
            ->line(new HtmlString($cmd))
            ->line(__('Hook execution output') . ': ')
            ->line(new HtmlString($output));
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

<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $model;
    private $options;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $model, array $options = [])
    {
        $this->model = $model;
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
        $mailMessage = (new MailMessage)
            ->subject(__('Payment received for :username at domain :tenant at :app', [
                'username' => $this->model->username,
                'tenant' => $this->model->domain_name,
                'app' => config('app.name')
            ]))
            ->greeting(__('Payment received'))
            ->line('**' . __('Payment received') . '**')
            ->line(__('Username') . ': **' . $this->model->username . '**')
            ->line(__('Domain') . ': **' . $this->model->domain_name . '**')
            ->line(__('Sum received') . ': **' . Arr::get($this->options, 'sum') . ' ' . Arr::get($this->options, 'currency') . '**')
            ->line(__('Total balance') . ': **' . $this->model->getCGRTBalanceAttribute() . ' ' . $this->model->getCGRTCurrencyAttribute() . '**');

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

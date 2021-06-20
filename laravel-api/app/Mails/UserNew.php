<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserNew extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $body = $this->markdown('emails.user.new')
            ->subject(__('A new user waiting for activation at :domain', ['domain' => $this->user->domain->getAttribute('domain_name')]))
            ->with([
                'user' => $this->user,
                'url' => \Request::root() . '/user/activate/' . $this->user['user_enabled'],
            ]);

        return $body;
    }
}

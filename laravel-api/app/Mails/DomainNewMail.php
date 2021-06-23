<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DomainNewMail extends Mailable
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
        $body = $this->markdown('emails.domain.new')
            ->subject(config('app.name') . ': ' . __('A new domain is created :domain', ['domain' => $this->user->domain->getAttribute('domain_name')]))
            ->with([
                'user' => $this->user,
            ]);

        return $body;
    }
}

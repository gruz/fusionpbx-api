<?php

namespace App\Events;

use App\Models\User;
use Gruz\FPBX\Events\AbstractEvent;

class PaymentReceivedEvent extends AbstractEvent
{
    public $user;

    public $options;

    public function __construct(User $user, array $options = [])
    {
        $this->user = $user;
        $this->options = $options;
    }
}

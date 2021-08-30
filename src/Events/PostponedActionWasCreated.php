<?php

namespace Gruz\FPBX\Events;

class PostponedActionWasCreated extends AbstractEvent
{
    public $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }
}

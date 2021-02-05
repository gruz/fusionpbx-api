<?php

use Api\Domain\Controllers\DomainController;

$router->post('/signup/domain', [DomainController::class, 'signup']);



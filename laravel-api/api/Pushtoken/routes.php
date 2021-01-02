<?php

$router->post('/pushtoken', [\Api\Pushtoken\Controllers\PushtokenController::class, 'create']);

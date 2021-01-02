<?php

use Api\Status\Controllers\StatusController;

$router->post('/status', [ StatusController::class,  'setStatus']);


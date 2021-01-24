<?php

$router->get('/test', [ \Infrastructure\Http\Controllers\FrontController::class, 'test' ], ['middleware' => 'web']);

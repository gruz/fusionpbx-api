<?php


$router->get('/test', [ \App\Http\Controllers\FrontController::class, 'test' ], ['middleware' => 'web']);

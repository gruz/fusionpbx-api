<?php

$router->get('/test', [ \Infrastructure\Http\Controllers\FrontController::class, 'test' ], ['middleware' => 'web']);

$router->get('/api/redoc', function(){
    return view('documenation.index');
});
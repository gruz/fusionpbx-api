<?php

/** @var Illuminate\Routing\Router $router */
$router->group(['middleware' => ['web']], function ($router) {
   /** @var Illuminate\Routing\Router $router */
   $router->get('/test', [ \Infrastructure\Http\Controllers\FrontController::class, 'test' ]);
});

$router->get('/api/redoc', function(){
    return view('documenation.index');
});

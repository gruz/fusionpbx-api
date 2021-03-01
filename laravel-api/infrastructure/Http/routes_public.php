<?php

/** @var Illuminate\Routing\Router $router */
$router->group(['middleware' => ['web']], function ($router) {

   /** @var Illuminate\Routing\Router $router */
   $router->get('/test', [ \Infrastructure\Http\Controllers\FrontController::class, 'test' ]);

   /**
    * _fuda_:
    *
    *   https://laracasts.com/index.php/discuss/channels/laravel/naming-routes-with-nameroutename?page=0
    *   The name() method does work. I use it all the time because I personally find it cleaner. 
    *   The problem is that you are trying to die and dump in your routes file. 
    *   Try it elsewhere (like in the controller), and you will see it work.
    *   When you use as in your array, it immediately adds it to the named list. 
    *   When you use the name method, it doesn't kick in until later in the Laravel lifecycle 
    *   when the RouteServiceProvider "refreshes" the named list.
    *   https://stackoverflow.com/questions/50054596/defining-custom-namespaces-on-routes-in-laravel-5-6
    */

   $router->namespace('\\Api\\User\\Controllers')->group(function ($router) {
      $router->get('/reset-password', [
         'uses' => 'UserController@resetPassword',
         'as' => 'password.reset',
      ]);
   
      $router->post('/reset-password', [
         'uses' => 'UserController@updatePassword',
         'as' => 'password.update',
      ]);
   
   });
});

$router->get('/api/redoc', function(){
    return view('documenation.index');
});
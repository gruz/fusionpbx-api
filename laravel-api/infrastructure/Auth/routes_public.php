<?php

$router->post('/login', ['uses' => 'LoginController@login',  'as' => 'login']);
$router->post('/login/refresh', 'LoginController@refresh');
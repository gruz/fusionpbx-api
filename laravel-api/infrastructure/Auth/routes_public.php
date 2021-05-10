<?php

$router->post('/api/login', ['uses' => 'LoginController@login',  'as' => 'login']);
$router->post('/api/login/refresh', 'LoginController@refresh');

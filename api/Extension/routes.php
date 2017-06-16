<?php

$router->get('/extensions', 'ExtensionController@getAll');
$router->get('/extension/{id}', 'ExtensionController@getById');
$router->post('/extension', 'ExtensionController@create');
$router->put('/extension/{id}', 'ExtensionController@update');
$router->delete('/extension/{id}', 'ExtensionController@delete');


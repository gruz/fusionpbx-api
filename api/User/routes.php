<?php

$router->get('/users', 'UserController@getAll');
$router->get('/user', 'UserController@getMe');
$router->get('/user/{id}', 'UserController@getById');
$router->post('/user', 'UserController@create');
$router->put('/user/{id}', 'UserController@update');
$router->delete('/user/{id}', 'UserController@delete');
$router->post('/user/{id}/groups', 'UserController@addGroups');
$router->put('/user/{id}/groups', 'UserController@setGroups');
$router->delete('/user/{id}/groups', 'UserController@removeGroups');

$router->get('/groups', 'GroupController@getAll');
$router->get('/group/{id}', 'GroupController@getById');
$router->post('/group', 'GroupController@create');
$router->put('/group/{id}', 'GroupController@update');
$router->delete('/group/{id}', 'GroupController@delete');


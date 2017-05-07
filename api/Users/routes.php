<?php

$router->get('/users', 'UserController@getAll');
$router->get('/users/{id}', 'UserController@getById');
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');
$router->post('/users/{id}/groups', 'UserController@addGroups');
$router->put('/users/{id}/groups', 'UserController@setGroups');
$router->delete('/users/{id}/groups', 'UserController@removeGroups');

$router->get('/groups', 'GroupController@getAll');
$router->get('/groups/{id}', 'GroupController@getById');
$router->post('/groups', 'GroupController@create');
$router->put('/groups/{id}', 'GroupController@update');
$router->delete('/groups/{id}', 'GroupController@delete');


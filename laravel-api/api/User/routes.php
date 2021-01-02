<?php

use Api\User\Controllers\UserController;
use Api\User\Controllers\GroupController;

$router->get('/users', [ UserController::class, 'getAll']);
$router->get('/user', [ UserController::class, 'getMe']);
$router->get('/user/{id}', [ UserController::class, 'getById']);
$router->post('/user', [ UserController::class, 'create']);
$router->put('/user/{id}', [ UserController::class, 'update']);
$router->delete('/user/{id}', [ UserController::class, 'delete']);
$router->post('/user/{id}/groups', [ UserController::class, 'addGroups']);
$router->put('/user/{id}/groups', [ UserController::class, 'setGroups']);
$router->delete('/user/{id}/groups', [ UserController::class, 'removeGroups']);

$router->get('/groups', [ GroupController::class, 'getAll']);
$router->get('/group/{id}', [ GroupController::class, 'getById']);
$router->post('/group', [ GroupController::class, 'create']);
$router->put('/group/{id}', [ GroupController::class, 'update']);
$router->delete('/group/{id}', [ GroupController::class, 'delete']);


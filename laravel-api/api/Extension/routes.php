<?php

$router->get('/extensions', [\Api\Extension\Controllers\ExtensionController::class,'getAll']);
$router->get('/extension/{id}', [\Api\Extension\Controllers\ExtensionController::class,'getById']);
$router->post('/extension', [\Api\Extension\Controllers\ExtensionController::class,'create']);
$router->put('/extension/{id}', [\Api\Extension\Controllers\ExtensionController::class,'update']);
$router->delete('/extension/{id}', [\Api\Extension\Controllers\ExtensionController::class,'delete']);


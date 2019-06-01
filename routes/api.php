<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->apiResource('groups', 'GroupsController')->only(['index']);
    $router->apiResource('characters', 'CharactersController');
    $router->apiResource('characters/{char}/parses', 'DpsParsesController');
    $router->apiResource('events', 'EventsController')->only(['index']);
    $router->apiResource('files', 'FilesController')->only(['store', 'destroy']);
    $router->apiResource('sets', 'SetsController')->only(['index']);
    $router->apiResource('skills', 'SkillsController')->only(['index']);
    $router->apiResource('content', 'ContentController')->only(['index']);
});

$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->get('users/@me', 'UsersController@me')->name('users@me');
});

$router->middleware(['auth:api', 'throttle'])->prefix('admin')->group(static function (Router $router) {
    $router->apiResource('characters', 'Admin\CharactersController')->only(['index', 'show', 'update']);
    $router->apiResource('parses', 'Admin\DpsParsesController')->only(['index', 'update', 'destroy']);
});

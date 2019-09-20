<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->apiResource('groups', 'GroupsController')->only(['index']);
    $router->apiResource('events', 'EventsController')->only(['index']);
    $router->apiResource('files', 'FilesController')->only(['store', 'destroy']);
    $router->apiResource('sets', 'SetsController')->only(['index']);
    $router->apiResource('skills', 'SkillsController')->only(['index']);
    $router->apiResource('content', 'ContentController')->only(['index']);
    $router->apiResource('users', 'UsersController')->only(['index']);
    $router->apiResource('users/{user}/characters', 'CharactersController')->only(['index', 'show']);
    $router->apiResource('characters', 'CharactersController')->only(['index', 'show']);
});

$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->get('users/@me', 'Auth\UsersController@me')->name('users@me');
    $router->put('users/@me', 'Auth\UsersController@updateMe')->name('users@update');
    $router->apiResource('users/@me/characters', 'Auth\CharactersController')->except(['show']);
    $router->apiResource('users/@me/characters/{char}/parses', 'Auth\DpsParsesController');
});

$router->middleware(['auth:api', 'throttle'])->prefix('admin')->group(static function (Router $router) {
    $router->apiResource('characters', 'Admin\CharactersController')->only(['update']);
    $router->apiResource('parses', 'Admin\DpsParsesController')->only(['index', 'update', 'destroy']);
});

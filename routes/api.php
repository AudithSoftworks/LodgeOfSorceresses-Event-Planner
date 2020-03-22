<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware(['api', 'throttle'])->group(static function (Router $router) {
    $router->apiResource('groups', 'GroupsController')->only(['index']);
    $router->apiResource('content', 'ContentController')->only(['index']);
});

$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->apiResource('events', 'EventsController')->only(['index']);
    $router->apiResource('files', 'FilesController')->only(['store', 'destroy']);
    $router->apiResource('sets', 'SetsController')->only(['index']);
    $router->apiResource('skills', 'SkillsController')->only(['index']);
    $router->apiResource('users', 'UsersController')->only(['index', 'show']);
    $router->apiResource('characters', 'CharactersController')->only(['index', 'show']);
    $router->apiResource('teams', 'TeamsController')->only(['index', 'store', 'show', 'update', 'destroy']);
    $router
        ->apiResource('teams/{team}/characters', 'TeamsCharactersController')
        ->names([
            'index' => 'teams.characters.index',
            'show' => 'teams.characters.show',
            'store' => 'teams.characters.store',
            'update' => 'teams.characters.update',
            'destroy' => 'teams.characters.destroy',
        ]);

    $router->get('users/@me', 'Auth\UsersController@me')->name('users@me');
    $router->put('users/@me', 'Auth\UsersController@updateMe')->name('users@update');
    $router
        ->apiResource('users/@me/characters', 'Auth\CharactersController')
        ->except(['show'])
        ->names([
            'index' => 'my-characters.index',
            'store' => 'my-characters.store',
            'update' => 'my-characters.update',
            'destroy' => 'my-characters.destroy',
        ]);
    $router->apiResource('users/@me/characters/{char}/parses', 'Auth\DpsParsesController');
});

$router->middleware(['auth:api', 'throttle'])->prefix('admin')->group(static function (Router $router) {
    $router->apiResource('characters', 'Admin\CharactersController')->only(['update']);
    $router
        ->apiResource('parses', 'Admin\DpsParsesController')
        ->only(['index', 'update', 'destroy'])
        ->names([
            'index' => 'admin.parses.index',
            'update' => 'admin.parses.update',
            'destroy' => 'admin.parses.destroy',
        ]);
});

<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->apiResource('chars', 'CharactersController');
    $router->apiResource('chars/{char}/parses', 'DpsParsesController')->except(['show']);
    $router->apiResource('events', 'EventsController')->only(['index']);
    $router->apiResource('sets', 'SetsController')->only(['index']);
    $router->apiResource('files', 'FilesController')->only(['store', 'destroy']);
});

$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router->get('discord-oauth-account', 'HomeController@getDiscordOauthAccount');
});

$router->middleware(['auth:api', 'throttle'])->prefix('admin')->group(static function (Router $router) {
    $router->apiResource('/', 'Admin\HomeController')->only(['index']);
    $router->apiResource('parses', 'Admin\DpsParsesController')->except(['store']);
});

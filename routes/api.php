<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware('auth:api')->group(function (Router $router) {
    $router->get('/user', function (Request $request) {
        return $request->user();
    });
    $router->resource('chars', 'CharactersController')->only(['index', 'store', 'destroy']);
    $router->resource('events', 'EventsController')->only(['index']);
    $router->resource('sets', 'SetsController')->only(['index']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware(['auth:api', 'throttle'])->group(function (Router $router) {
    $router->resource('chars', 'CharactersController')->except(['create', 'show']);
    $router->resource('chars/{char}/parses', 'DpsParsesController')->except(['create', 'show']);
    $router->resource('events', 'EventsController')->only(['index']);
    $router->resource('sets', 'SetsController')->only(['index']);
    $router->resource('files', 'FilesController')->only(['store', 'destroy']);
});

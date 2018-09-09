<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** @var \Illuminate\Routing\Router $router */
$router->resource('chars', 'CharactersController')->only(['store']);
$router->resource('events', 'EventsController')->only(['index']);
$router->resource('sets', 'SetsController')->only(['index']);

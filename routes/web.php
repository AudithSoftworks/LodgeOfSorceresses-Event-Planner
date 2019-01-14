<?php

use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*------------------------------------------------------------------------
 | Non-localized, generic routes (such as those for admin panel etc).
 *-----------------------------------------------------------------------*/

/** @var \Illuminate\Routing\Router $router */
$router->get('oauth/to/{provider}', ['uses' => 'Auth\LoginController@handleOAuthRedirect', 'as' => 'oauth.to']);
$router->get('oauth/from/{provider}', ['uses' => 'Auth\LoginController@handleOAuthReturn', 'as' => 'oauth.from']);

/*---------------------------------------------------------------------------------------------------------
 | Register localized routes with locale-prefices (in case of default locale, no prefix is attached).
 *--------------------------------------------------------------------------------------------------------*/

// Localized routes.
$router->middleware('auth')->group(function (Router $router) {
    $router->get('logout', ['uses' => 'Auth\LoginController@logout', 'as' => 'logout']);
    $router->get('', ['uses' => 'HomeController@index', 'as' => 'home']);
    $router->resource('chars', 'CharactersController')->only('index');
});

<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->get('oauth/to/{provider}', ['uses' => 'Auth\LoginController@handleOAuthRedirect', 'as' => 'oauth.to']);
$router->get('oauth/from/{provider}', ['uses' => 'Auth\LoginController@handleOAuthReturn', 'as' => 'oauth.from']);

$router->middleware('auth')->group(function (Router $router) {
    $router->get('logout', ['uses' => 'Auth\LoginController@logout', 'as' => 'logout']);
    $router->get('{any}', ['uses' => 'HomeController@index', 'as' => 'home'])->where('any', '(?!api).*');
});

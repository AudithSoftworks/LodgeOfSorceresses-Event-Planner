<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->get('oauth/to/{provider}', ['uses' => 'LoginController@handleOAuthRedirect', 'as' => 'oauth.to']);
$router->get('oauth/from/{provider}', ['uses' => 'LoginController@handleOAuthReturn', 'as' => 'oauth.from']);

$router->middleware('auth')->group(static function (Router $router) {
    $router->get('logout', ['uses' => 'LoginController@logout', 'as' => 'logout']);
    $router->get('{any}', ['uses' => 'HomeController@index', 'as' => 'home'])->where('any', '(?!api).*');
});

<?php

use Illuminate\Routing\Router;

/** @var \Illuminate\Routing\Router $router */
$router->middleware(['api', 'throttle'])->group(static function (Router $router) {
    $router->apiResource('content', 'ContentController')->only(['index']);
});

$router->middleware(['auth:api', 'throttle'])->group(static function (Router $router) {
    $router
        ->get('onboarding/members/content/by-step/{step}', 'OnboardingController@getCmsContentByStepForMemberOnboarding')
        ->name('onboarding.members.content');
    $router
        ->get('onboarding/soulshriven/content/by-step/{step}', 'OnboardingController@getCmsContentByStepForSoulshrivenOnboarding')
        ->name('onboarding.soulshriven.content');
    $router
        ->post('onboarding/finalize', 'OnboardingController@finalizeOnboarding')
        ->name('onboarding.finalize');
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

    $router->get('users/@me', 'Auth\UsersController@me')->name('@me.show');
    $router->put('users/@me', 'Auth\UsersController@updateMe')->name('@me.update');
    $router->delete('users/@me', 'Auth\UsersController@deleteMe')->name('@me.delete');
    $router
        ->apiResource('users/@me/characters', 'Auth\CharactersController')
        ->except(['show'])
        ->names([
            'index' => '@me.characters.index',
            'store' => '@me.characters.store',
            'update' => '@me.characters.update',
            'destroy' => '@me.characters.destroy',
        ]);
    $router
        ->apiResource('users/@me/characters/{char}/parses', 'Auth\DpsParsesController')
        ->only(['store', 'destroy'])
        ->names([
            'store' => '@me.characters.parses.store',
            'destroy' => '@me.characters.parses.destroy',
        ]);
});

$router->middleware(['auth:api', 'throttle'])->prefix('admin')->group(static function (Router $router) {
    $router
        ->apiResource('characters', 'Admin\CharactersController')
        ->only(['update'])
        ->names(['update' => 'admin.characters.update']);
    $router
        ->apiResource('parses', 'Admin\DpsParsesController')
        ->only(['index', 'update', 'destroy'])
        ->names([
            'index' => 'admin.parses.index',
            'update' => 'admin.parses.update',
            'destroy' => 'admin.parses.destroy',
        ]);
});

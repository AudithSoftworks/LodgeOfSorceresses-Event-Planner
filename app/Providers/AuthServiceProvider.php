<?php

namespace App\Providers;

use App\Models\Character;
use App\Models\DpsParse;
use App\Policies\CharacterPolicy;
use App\Policies\DpsParsePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Character::class => CharacterPolicy::class,
        DpsParse::class => DpsParsePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::withoutCookieSerialization();
    }
}

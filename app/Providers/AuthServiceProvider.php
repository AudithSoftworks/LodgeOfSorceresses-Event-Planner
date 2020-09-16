<?php

namespace App\Providers;

use App\Models\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Passport::withoutCookieSerialization();

        Gate::define('is-admin', UserPolicy::class . '@admin');
        Gate::define('has-app-access', UserPolicy::class . '@user');
        Gate::define('is-member', UserPolicy::class . '@member');
        Gate::define('is-soulshriven', UserPolicy::class . '@soulshriven');
        Gate::define('is-logged-in', UserPolicy::class . '@limited');
    }
}

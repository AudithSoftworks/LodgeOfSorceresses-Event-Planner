<?php namespace App\Providers;

use App\Services\TeamsAndEligibility;
use Illuminate\Support\ServiceProvider;

class TeamsAndEligibilityServiceProvider extends ServiceProvider
{
    /**
     * Deferring the loading of a provider improves performance of the application,
     * since it is not loaded from the filesystem on every request.
     */
    protected bool $defer = true;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('teams.eligibility', TeamsAndEligibility::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['teams.eligibility'];
    }
}

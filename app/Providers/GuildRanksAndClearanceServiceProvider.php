<?php namespace App\Providers;

use App\Services\GuildRanksAndClearance;
use Illuminate\Support\ServiceProvider;

class GuildRanksAndClearanceServiceProvider extends ServiceProvider
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
        $this->app->singleton('guild.ranks.clearance', GuildRanksAndClearance::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['guild.ranks.clearance'];
    }
}

<?php

namespace App\Providers;

use App\Services\DiscordApi;
use Illuminate\Support\ServiceProvider;

class DiscordApiServiceProvider extends ServiceProvider
{
    /**
     * Deferring the loading of a provider improves performance of the application,
     * since it is not loaded from the filesystem on every request.
     */
    protected bool $defer = true;

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('discord.api', DiscordApi::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['discord.api'];
    }
}

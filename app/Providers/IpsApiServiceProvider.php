<?php

namespace App\Providers;

use App\Services\IpsApi;
use Illuminate\Support\ServiceProvider;

class IpsApiServiceProvider extends ServiceProvider
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
        $this->app->singleton('ips.api', IpsApi::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['ips.api'];
    }
}

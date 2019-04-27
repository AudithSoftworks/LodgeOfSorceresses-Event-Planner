<?php

namespace App\Providers;

use App\Services\PmgApi;
use Illuminate\Support\ServiceProvider;

class PmgApiServiceProvider extends ServiceProvider
{
    /**
     * Deferring the loading of a provider improves performance of the application,
     * since it is not loaded from the filesystem on every request.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('pmg.api', PmgApi::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['pmg.api'];
    }
}

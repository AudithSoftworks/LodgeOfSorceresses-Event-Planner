<?php

namespace App\Providers;

use App\Extensions\Socialite\DiscordProvider;
use App\Extensions\Socialite\IpsProvider;
use App\Services\LaravelMix;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Foundation\Mix;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Socialite\Contracts\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $this->extendSocialiteWithAdditionalProviders();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreMigrations();

        if (!$this->app->environment('production')) {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->bind(Mix::class, LaravelMix::class);
        }
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function extendSocialiteWithAdditionalProviders(): void
    {
        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'ips',
            static function ($app) use ($socialite) {
                $config = $app['config']['services.ips'];
                return $socialite->buildProvider(IpsProvider::class, $config);
            }
        );
        $socialite->extend(
            'discord',
            static function ($app) use ($socialite) {
                $config = $app['config']['services.discord'];
                return $socialite->buildProvider(DiscordProvider::class, $config);
            }
        );
    }
}

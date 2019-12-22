<?php

namespace App\Providers;

use App\Extensions\Socialite\DiscordProvider;
use App\Extensions\Socialite\IpsProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Socialite\Contracts\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $this->extendSocialiteWithAdditionalProviders();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Passport::ignoreMigrations();

        if (!app()->environment('production')) {
            app()->register(IdeHelperServiceProvider::class);
        }
    }

    private function extendSocialiteWithAdditionalProviders(): void
    {
        $socialite = app(Factory::class);
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

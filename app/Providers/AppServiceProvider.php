<?php namespace App\Providers;

use App\Extensions\Socialite\IpsProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->bootIpsSocialiteProvider();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (app()->environment('local', 'testing')) {
            app()->register(DuskServiceProvider::class);
        }
        Passport::ignoreMigrations();
    }

    private function bootIpsSocialiteProvider()
    {
        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'ips',
            function ($app) use ($socialite) {
                $config = $app['config']['services.ips'];
                return $socialite->buildProvider(IpsProvider::class, $config);
            }
        );
    }
}

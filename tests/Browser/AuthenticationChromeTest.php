<?php namespace App\Tests\Browser;

use App\Tests\DuskChromeTestCase;
use Laravel\Dusk\Browser;

class AuthenticationChromeTest extends DuskChromeTestCase
{
    public function setUp(): void
    {
        // Migrations should run only once, before application is created (the moment when $this->app == null).
        if ($this->app === null) {
            $this->afterApplicationCreated(function () {
                $this->artisan('migrate:reset');
                $this->artisan('migrate');
            });
        }

        parent::setUp();
    }

    public function testAuthenticateMiddleware(): void
    {
        $this->browse(static function (Browser $browser) {
            $browser->visit('/oauth/clients');
            $browser->waitForText('Login');
            $browser->assertPathIs('/en/login');
        });
    }

    public function testHome(): void
    {
        $this->browse(static function (Browser $browser) {
            $browser->visit('/');
            $browser->assertPathIs('/en');
        });
    }
}

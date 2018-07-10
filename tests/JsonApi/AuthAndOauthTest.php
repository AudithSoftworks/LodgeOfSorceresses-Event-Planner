<?php namespace App\Tests\JsonApi;

use App\Exceptions\Common\ValidationException;
use App\Exceptions\Users\TokenNotValidException;
use App\Tests\IlluminateTestCase;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthAndOauthTest extends IlluminateTestCase
{
    /** @var bool */
    public static $migrated = false;

    /** @var \stdClass */
    public static $oauthClientData;

    /** @var string */
    public static $passwordResetToken;

    public function setUp()
    {
        // Migrations should run only once, before application is created (the moment when $this->app == null).
        if (!static::$migrated) {
            $this->afterApplicationCreated(function () {
                $this->artisan('migrate:reset');
                $this->artisan('migrate');
            });
            static::$migrated = true;
        }

        parent::setUp();

        if (is_null(static::$oauthClientData)) {
            $this->artisan('passport:client', ['--password' => true, '--name' => 'PhpUnitTestClient']);
            static::$oauthClientData = app('db')->table('oauth_clients')->select('id', 'secret')->where('name', '=', 'PhpUnitTestClient')->first();
        }
    }

    /**
     * Tests 'POST /api/v1/register' endpoint for success.
     */
    public function testRegisterWithPostMethodForSuccess()
    {
        $credentials = ['email' => 'john.doe@example.com', 'password' => 'theWeakestPasswordEver', 'password_confirmation' => 'theWeakestPasswordEver'];
        $response = $this->json('POST', '/api/v1/register', $credentials);
        $response->assertStatus(201);
        $response->assertJson([
            'email' => 'john.doe@example.com'
        ]);
        $this->assertDatabaseHas('users', ['email' => $credentials['email']]);
    }
}

<?php

namespace App\Tests\Integration\JsonApi;

use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SetsControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    protected static bool $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate');
            Artisan::call('pmg:sets');
            static::$setupHasRunOnce = true;
        }
    }

    public function testIndexForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/sets');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        $response->assertJsonPath('message', 'This action is unauthorized.');
    }

    public function testIndexForSuccess(): void
    {
        $this->stubSoulshrivenUser();

        $response = $this
            ->actingAs(static::$soulshriven)
            ->withoutMiddleware()
            ->getJson('/api/sets');
        $response->assertStatus(JsonResponse::HTTP_OK);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        $firstEntry = array_shift($responseOriginalContent);
        static::assertNotEmpty($firstEntry['name']);
    }
}

<?php

namespace App\Tests\Integration\JsonApi;

use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SetsControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    /**
     * @var bool
     */
    protected static $setupHasRunOnce = false;

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
        $this->assertNotNull($responseOriginalContent);
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
        $this->assertNotNull($responseOriginalContent);
        $this->assertIsIterable($responseOriginalContent);
        $firstEntry = array_shift($responseOriginalContent);
        $this->assertEquals('Death\'s Wind', $firstEntry['name']);
    }

    public function testShowForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/sets/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testStoreForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/sets', []);
        $response->assertStatus(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testUpdateForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->putJson('/api/sets/1', []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testDestroyForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->deleteJson('/api/sets/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }
}

<?php

namespace App\Tests\Integration\JsonApi;

use App\Tests\IlluminateTestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ContentControllerTest extends IlluminateTestCase
{
    /**
     * @var bool
     */
    protected static $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate');
            Artisan::call('db:seed', ['--class' => \ContentTableSeeder::class]);
            static::$setupHasRunOnce = true;
        }
    }

    public function testIndexForSuccess(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/content');
        $response->assertStatus(JsonResponse::HTTP_OK);
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertNotNull($responseOriginalContent);
        $this->assertIsIterable($responseOriginalContent);
        $firstEntry = array_shift($responseOriginalContent);
        $this->assertNotEmpty($firstEntry['name']);
        $lastEntry = array_pop($responseOriginalContent);
        $this->assertNotEmpty($lastEntry['name']);
    }

    public function testShowForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/content/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testStoreForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/content', []);
        $response->assertStatus(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testUpdateForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->putJson('/api/content/1', []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testDestroyForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->deleteJson('/api/content/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }
}

<?php

namespace App\Tests\Integration\JsonApi;

use App\Tests\IlluminateTestCase;
use Database\Seeders\ContentTableSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ContentControllerTest extends IlluminateTestCase
{
    protected static bool $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate');
            Artisan::call('db:seed', ['--class' => ContentTableSeeder::class]);
            static::$setupHasRunOnce = true;
        }
    }

    public function testIndexForSuccess(): void
    {
        $response = $this->getJson('/api/content');
        $response->assertStatus(JsonResponse::HTTP_OK);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        $firstEntry = array_shift($responseOriginalContent);
        static::assertNotEmpty($firstEntry['name']);
        $lastEntry = array_pop($responseOriginalContent);
        static::assertNotEmpty($lastEntry['name']);
    }

    public function testShowForFailure(): void
    {
        $response = $this->getJson('/api/content/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testStoreForFailure(): void
    {
        $response = $this->postJson('/api/content', []);
        $response->assertStatus(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testUpdateForFailure(): void
    {
        $response = $this->putJson('/api/content/1', []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testDestroyForFailure(): void
    {
        $response = $this->deleteJson('/api/content/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }
}

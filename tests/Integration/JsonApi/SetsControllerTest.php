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
        $response = $this->getJson('/api/sets');
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        $guestUser = $this->stubCustomUserWithCustomCharacters();
        $response = $this
            ->actingAs($guestUser, 'api')
            ->getJson('/api/sets');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
    }

    public function testIndexForSuccess(): void
    {
        $soulshrivenUser = $this->stubCustomUserWithCustomCharacters('soulshriven');

        $response = $this
            ->actingAs($soulshrivenUser, 'api')
            ->getJson('/api/sets');
        $response->assertStatus(JsonResponse::HTTP_OK);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        $firstEntry = array_shift($responseOriginalContent);
        static::assertNotEmpty($firstEntry['name']);
    }
}

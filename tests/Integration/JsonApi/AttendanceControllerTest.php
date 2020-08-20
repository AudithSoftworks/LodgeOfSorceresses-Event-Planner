<?php

namespace App\Tests\Integration\JsonApi;

use App\Models\Attendance;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class AttendanceControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    protected static bool $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate');
            static::$setupHasRunOnce = true;
        }
    }

    public function testIndexForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/attendances/1');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        $response->assertJsonPath('message', 'This action is unauthorized.');
    }

    public function testIndexForSuccess(): void
    {
        $this->stubSoulshrivenUser();

        /** @var Attendance $attendance */
        $attendance = factory(Attendance::class)->create();
        $attendance->attendees()->sync([static::$soulshriven->id]);

        $response = $this
            ->actingAs(static::$soulshriven)
            ->withoutMiddleware()
            ->getJson('/api/attendances/' . static::$soulshriven->id);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Support\Collection $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        static::assertCount(1, $responseOriginalContent);
        $responseAsArray = $responseOriginalContent->toArray();
        $firstEntry = $responseAsArray[0];
        static::assertNotEmpty($firstEntry['attendees']);
        static::assertIsIterable($firstEntry['attendees']);
        static::assertCount(1, $firstEntry['attendees']);
    }
}

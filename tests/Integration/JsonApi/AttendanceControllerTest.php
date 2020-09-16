<?php

namespace App\Tests\Integration\JsonApi;

use App\Models\Attendance;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Carbon\CarbonImmutable;
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

    /**
     * @throws \Exception
     */
    public function testIndexForSuccess(): void
    {
        $this->stubSoulshrivenUser();

        /** @var Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'text' => '2 weeks ago',
            'created_at' => (new CarbonImmutable())->subWeeks(2),
        ]);
        $attendance->attendees()->sync([static::$soulshriven->id]);
        $attendance = Attendance::factory()->create([
            'text' => '4 weeks ago',
            'created_at' => (new CarbonImmutable())->subWeeks(4),
        ]);
        $attendance->attendees()->sync([static::$soulshriven->id]);
        $attendance = Attendance::factory()->create([
            'text' => '4 weeks 1 day ago',
            'created_at' => (new CarbonImmutable())->subWeeks(4)->subDay(),
        ]);
        $attendance->attendees()->sync([static::$soulshriven->id]);
        $attendance = Attendance::factory()->create([
            'text' => '6 weeks ago',
            'created_at' => (new CarbonImmutable())->subWeeks(5),
        ]);
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
        $entries = $responseOriginalContent->toArray();
        static::assertEquals('2 weeks ago', $entries[0]['text']);
        static::assertNotEmpty($entries[0]['attendees']);
        static::assertIsIterable($entries[0]['attendees']);
        static::assertCount(1, $entries[0]['attendees']);

        $createdAtInAtomFormat = (new CarbonImmutable($entries[0]['created_at']))->toAtomString();
        $response = $this
            ->actingAs(static::$soulshriven)
            ->withoutMiddleware()
            ->getJson('/api/attendances/' . static::$soulshriven->id . '?b=' . urlencode($createdAtInAtomFormat));
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Support\Collection $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        static::assertCount(2, $responseOriginalContent);
        $entries = $responseOriginalContent->toArray();
        static::assertEquals('4 weeks ago', $entries[0]['text']);
        static::assertEquals('4 weeks 1 day ago', $entries[1]['text']);
        static::assertNotEmpty($entries[0]['attendees']);
        static::assertIsIterable($entries[0]['attendees']);
        static::assertCount(1, $entries[0]['attendees']);

        $createdAtInAtomFormat = (new CarbonImmutable($entries[1]['created_at']))->toAtomString();
        $response = $this
            ->actingAs(static::$soulshriven)
            ->withoutMiddleware()
            ->getJson('/api/attendances/' . static::$soulshriven->id . '?b=' . urlencode($createdAtInAtomFormat));
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Support\Collection $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        static::assertCount(1, $responseOriginalContent);
        $entries = $responseOriginalContent->toArray();
        static::assertEquals('6 weeks ago', $entries[0]['text']);
        static::assertNotEmpty($entries[0]['attendees']);
        static::assertIsIterable($entries[0]['attendees']);
        static::assertCount(1, $entries[0]['attendees']);
    }
}

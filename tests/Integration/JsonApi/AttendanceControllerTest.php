<?php

namespace App\Tests\Integration\JsonApi;

use App\Models\Attendance;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
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
        $response = $this->getJson('/api/attendances/1');
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        $guestUser = $this->stubCustomUserWithCustomCharacters();
        $response = $this
            ->actingAs($guestUser, 'api')
            ->getJson(sprintf('/api/attendances/%d', $guestUser->id));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
    }

    /**
     * @throws \Exception
     */
    public function testIndexForSuccess(): void
    {
        $tierTwoSoulshrivenUser = $this->stubCustomUserWithCustomCharacters('soulshriven', 2, RoleTypes::ROLE_TANK, ClassTypes::CLASS_NECROMANCER);

        /** @var Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'text' => '2 weeks ago Monday',
            'created_at' => (new CarbonImmutable())->subWeeks(2)->startOf('week'),
        ]);
        $attendance->attendees()->sync([$tierTwoSoulshrivenUser->id]);
        $attendance = Attendance::factory()->create([
            'text' => '4 weeks ago Wednesday',
            'created_at' => (new CarbonImmutable())->subWeeks(4)->startOf('week')->addDays(2),
        ]);
        $attendance->attendees()->sync([$tierTwoSoulshrivenUser->id]);
        $attendance = Attendance::factory()->create([
            'text' => '5 weeks ago Tuesday',
            'created_at' => (new CarbonImmutable())->subWeeks(5)->startOf('week')->addDays(1),
        ]);
        $attendance->attendees()->sync([$tierTwoSoulshrivenUser->id]);
        $attendance = Attendance::factory()->create([
            'text' => '5 weeks ago Sunday',
            'created_at' => (new CarbonImmutable())->subWeeks(5)->startOf('week')->addDays(6),
        ]);
        $attendance->attendees()->sync([$tierTwoSoulshrivenUser->id]);

        $response = $this
            ->actingAs($tierTwoSoulshrivenUser, 'api')
            ->getJson(sprintf('/api/attendances/%d', $tierTwoSoulshrivenUser->id));
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Support\Collection $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        static::assertCount(1, $responseOriginalContent);
        $entries = $responseOriginalContent->toArray();
        static::assertEquals('2 weeks ago Monday', $entries[0]['text']);
        static::assertNotEmpty($entries[0]['attendees']);
        static::assertIsIterable($entries[0]['attendees']);
        static::assertCount(1, $entries[0]['attendees']);

        $createdAtInAtomFormat = (new CarbonImmutable($entries[0]['created_at']))->toRfc3339String(true);
        $response = $this
            ->actingAs($tierTwoSoulshrivenUser, 'api')
            ->getJson(sprintf(
                '/api/attendances/%d?b=%s',
                $tierTwoSoulshrivenUser->id,
                urlencode($createdAtInAtomFormat)
            ));
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Support\Collection $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        static::assertCount(1, $responseOriginalContent);
        $entries = $responseOriginalContent->toArray();
        static::assertEquals('4 weeks ago Wednesday', $entries[0]['text']);
        static::assertNotEmpty($entries[0]['attendees']);
        static::assertIsIterable($entries[0]['attendees']);
        static::assertCount(1, $entries[0]['attendees']);

        $createdAtInAtomFormat = (new CarbonImmutable($entries[0]['created_at']))->toRfc3339String(true);
        $response = $this
            ->actingAs($tierTwoSoulshrivenUser, 'api')
            ->getJson(sprintf(
                '/api/attendances/%d?b=%s',
                $tierTwoSoulshrivenUser->id,
                urlencode($createdAtInAtomFormat)
            ));
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Support\Collection $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        static::assertIsIterable($responseOriginalContent);
        static::assertCount(2, $responseOriginalContent);
        $entries = $responseOriginalContent->toArray();
        static::assertEquals('5 weeks ago Sunday', $entries[0]['text']);
        static::assertEquals('5 weeks ago Tuesday', $entries[1]['text']);
        static::assertNotEmpty($entries[0]['attendees']);
        static::assertIsIterable($entries[0]['attendees']);
        static::assertCount(1, $entries[0]['attendees']);
    }
}

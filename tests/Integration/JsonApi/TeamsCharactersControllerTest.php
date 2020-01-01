<?php

namespace App\Tests\Integration\JsonApi;

use App\Events\Team\TeamUpdated;
use App\Models\Team;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsTeamStubs;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

class TeamsCharactersControllerTest extends IlluminateTestCase
{
    use NeedsTeamStubs, NeedsUserStubs;

    /**
     * @var \App\Models\Team
     */
    protected static $team;

    public static function setUpBeforeClass(): void
    {
        app(ConsoleKernel::class)->call('migrate:refresh');
    }

    public function testStoreForSuccess(): void
    {
        Event::fake([TeamUpdated::class]);

        static::$team = $this->stubTierXAdminUserTeam(2);
        $tierOneMemberUser = $this->stubTierXMemberUser(1);
        $tierTwoMemberUser = $this->stubTierXMemberUser(2);
        $tierThreeMemberUser = $this->stubTierXMemberUser(3);
        $tierFourMemberUser = $this->stubTierXMemberUser(4);

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->postJson('/api/teams/' . static::$team->id . '/characters', [
                'characterIds' => [
                    static::$team->ledBy->characters->first()->id,
                    $tierOneMemberUser->characters->first()->id,
                    $tierTwoMemberUser->characters->first()->id,
                    $tierThreeMemberUser->characters->first()->id,
                    $tierFourMemberUser->characters->first()->id,
                ],
            ]);
        $response->assertStatus(JsonResponse::HTTP_CREATED);
        /** @var \App\Models\Team $teamFromResponse */
        $teamFromResponse = $response->getOriginalContent();
        $this->assertInstanceOf(Team::class, $teamFromResponse);
        $this->assertTrue($teamFromResponse->exists);
        $this->assertFalse($teamFromResponse->wasRecentlyCreated);
        $this->assertIsInt($teamFromResponse->id);
        $this->assertEquals(4, $teamFromResponse->members->count());
        foreach ($teamFromResponse->members as $member) {
            $this->assertGreaterThanOrEqual(2, $member->approved_for_tier);
        }

        Event::assertDispatched(TeamUpdated::class);
    }

    public function testUpdateForSuccess(): void
    {
        Event::fake([TeamUpdated::class]);

        $teamLeader = static::$team->ledBy->loadMissing('characters');
        /** @var \App\Models\Character $teamLeaderCharacter */
        $teamLeaderCharacter = $teamLeader->characters->first();

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->putJson('/api/teams/' . static::$team->id . '/characters/' . $teamLeaderCharacter->id, [
                'accepted_terms' => '1',
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Team $teamFromResponse */
        $teamFromResponse = $response->getOriginalContent();
        $this->assertInstanceOf(Team::class, $teamFromResponse);
        $this->assertTrue($teamFromResponse->exists);
        $this->assertFalse($teamFromResponse->wasRecentlyCreated);
        $this->assertIsInt($teamFromResponse->id);
        $this->assertEquals(4, $teamFromResponse->members->count());
        foreach ($teamFromResponse->members as $character) {
            $this->assertGreaterThanOrEqual(2, $character->approved_for_tier);
            if ($character->id === $teamLeaderCharacter->id) {
                $this->assertTrue((bool)$character->teamMembership->status);
                $this->assertTrue((bool)$character->teamMembership->accepted_terms);
            } else {
                $this->assertFalse((bool)$character->teamMembership->status);
                $this->assertFalse((bool)$character->teamMembership->accepted_terms);
            }
        }

        Event::assertDispatched(TeamUpdated::class);
    }

    public function testShowForSuccess(): void
    {
        $teamLeader = static::$team->ledBy->loadMissing('characters');
        /** @var \App\Models\Character $teamLeaderCharacter */
        $teamLeaderCharacter = $teamLeader->characters->first();

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->getJson('/api/teams/' . static::$team->id . '/characters/' . $teamLeaderCharacter->id);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $teamMembershipPivotFromResponse */
        $teamMembershipPivotFromResponse = $response->getOriginalContent();
        $this->assertInstanceOf(Pivot::class, $teamMembershipPivotFromResponse);
        $response->assertJsonPath('status', 1);
        $response->assertJsonPath('accepted_terms', 1);
    }
}

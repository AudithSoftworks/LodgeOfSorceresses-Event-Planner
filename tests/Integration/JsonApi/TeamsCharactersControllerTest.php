<?php

namespace App\Tests\Integration\JsonApi;

use App\Events\Team\MemberInvited;
use App\Events\Team\MemberJoined;
use App\Events\Team\MemberRemoved;
use App\Events\Team\TeamUpdated;
use App\Models\Character;
use App\Models\Team;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsTeamStubs;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class TeamsCharactersControllerTest extends IlluminateTestCase
{
    use NeedsTeamStubs, NeedsUserStubs;

    protected static bool $setupHasRunOnce = false;

    protected static Team $team;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate:fresh');
            static::$setupHasRunOnce = true;
        }
    }

    public function testIndexForFailure(): void
    {
        static::$team = $this->stubTierXAdminUserTeam(2);

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/teams/1000/characters');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Non existent team
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->getJson('/api/teams/1000/characters');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team not found!');
    }

    public function testIndexForEmpty(): void
    {
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->getJson('/api/teams/' . static::$team->id . '/characters');
        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonCount(0);
    }

    public function testStoreForFailure(): void
    {
        Event::fake([MemberInvited::class, TeamUpdated::class]);
        $this->stubTierFourAdminUser();

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/teams/' . static::$team->id . '/characters', [
                'characterIds' => [
                    static::$team->ledBy->characters->first()->id,
                ],
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Invalid input
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->postJson('/api/teams/' . static::$team->id . '/characters');
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonFragment(['characterIds' => [0 => 'Select the character(s) to be added to the team.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->postJson('/api/teams/' . static::$team->id . '/characters', [
                'characterIds' => ['a'],
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonFragment(['characterIds.0' => [0 => 'The characterIds.0 must be a number.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->postJson('/api/teams/' . static::$team->id . '/characters', [
                'characterIds' => [1000],
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonFragment(['characterIds.0' => [0 => 'No such characters exist.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case 3: Team doesn't exist
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->postJson('/api/teams/1000/characters', [
                'characterIds' => [
                    static::$team->ledBy->characters->first()->id,
                ],
            ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team not found!');

        # Case 4: Non-leader/non-creator attempting to invite.
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->postJson('/api/teams/' . static::$team->id . '/characters', [
                'characterIds' => [
                    static::$team->ledBy->characters->first()->id,
                ],
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $response->assertJsonPath('message', 'Only team leader or creator can invite new members!');

        Event::assertNotDispatched(TeamUpdated::class);
        Event::assertNotDispatched(MemberInvited::class);
    }

    public function testStoreForSuccess(): void
    {
        # Case 1: Regular store
        Event::fake([MemberInvited::class, TeamUpdated::class]);
        $tierOneMemberUser = $this->stubCustomUserWithCustomCharacters(1);
        $tierTwoMemberUser = $this->stubCustomUserWithCustomCharacters(2);
        $tierThreeMemberUser = $this->stubCustomUserWithCustomCharacters(3);
        $tierFourMemberUser = $this->stubCustomUserWithCustomCharacters(4);
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
        static::assertInstanceOf(Team::class, $teamFromResponse);
        static::assertTrue($teamFromResponse->exists);
        static::assertFalse($teamFromResponse->wasRecentlyCreated);
        static::assertIsInt($teamFromResponse->id);
        static::assertEquals(4, $teamFromResponse->members->count());
        foreach ($teamFromResponse->members as $member) {
            static::assertGreaterThanOrEqual(2, $member->approved_for_tier);
        }
        Event::assertDispatched(TeamUpdated::class);
        Event::assertDispatched(MemberInvited::class);

        # Case 2: Storing the same people shouldn't do any changes.
        Event::fake([MemberInvited::class, TeamUpdated::class]);
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
        Event::assertDispatched(TeamUpdated::class);
        Event::assertNotDispatched(MemberInvited::class);
    }

    public function testUpdateForFailure(): void
    {
        Event::fake([MemberJoined::class, MemberRemoved::class, TeamUpdated::class]);

        $teamLeader = static::$team->ledBy->loadMissing('characters');
        /** @var \App\Models\Character $teamLeaderCharacter */
        $teamLeaderCharacter = $teamLeader->characters->first();
        $this->stubTierFourAdminUser();

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->putJson('/api/teams/' . static::$team->id . '/characters/' . $teamLeaderCharacter->id, [
                'accepted_terms' => '1',
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Invalid input
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->putJson('/api/teams/' . static::$team->id . '/characters/' . $teamLeaderCharacter->id);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.accepted_terms.0', 'The accepted terms field is required.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->putJson('/api/teams/' . static::$team->id . '/characters/' . $teamLeaderCharacter->id, [
                'accepted_terms' => '3',
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.accepted_terms.0', 'Please make sure you accept the terms of membership.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case 3: Team membership record doesn't exist
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->putJson('/api/teams/' . static::$team->id . '/characters/1000', [
                'accepted_terms' => '1',
            ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team has no such member!');

        # Case 4: Managing someone else's team membership options
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/teams/' . static::$team->id . '/characters/' . $teamLeaderCharacter->id, [
                'accepted_terms' => '1',
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $response->assertJsonPath('message', 'You can\'t manage someone else\'s team membership options!');

        # Case 5: Team doesn't exist
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->putJson('/api/teams/1000/characters/' . $teamLeaderCharacter->id, [
                'accepted_terms' => '1',
            ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team not found!');

        Event::assertNotDispatched(TeamUpdated::class);
        Event::assertNotDispatched(MemberJoined::class);
        Event::assertNotDispatched(MemberRemoved::class);
    }

    public function testUpdateForSuccess(): void
    {
        Event::fake([MemberJoined::class, TeamUpdated::class]);

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
        static::assertInstanceOf(Team::class, $teamFromResponse);
        static::assertTrue($teamFromResponse->exists);
        static::assertFalse($teamFromResponse->wasRecentlyCreated);
        static::assertIsInt($teamFromResponse->id);
        static::assertEquals(4, $teamFromResponse->members->count());
        foreach ($teamFromResponse->members as $character) {
            static::assertGreaterThanOrEqual(2, $character->approved_for_tier);
            if ($character->id === $teamLeaderCharacter->id) {
                static::assertTrue((bool)$character->teamMembership->status);
                static::assertTrue((bool)$character->teamMembership->accepted_terms);
            } else {
                static::assertFalse((bool)$character->teamMembership->status);
                static::assertFalse((bool)$character->teamMembership->accepted_terms);
            }
        }

        Event::assertDispatched(TeamUpdated::class);
        Event::assertDispatched(MemberJoined::class);
    }

    public function testShowForFailure(): void
    {
        # Case 1: Team doesn't exist
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->getJson('/api/teams/1000/characters/1000');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team not found!');

        # Case 1: Team doesn't exist
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->getJson('/api/teams/' . static::$team->id . '/characters/1000');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team has no such member!');
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
        static::assertInstanceOf(Pivot::class, $teamMembershipPivotFromResponse);
        $response->assertJsonPath('status', $_ENV['DB_CONNECTION'] === 'pgsql' ? true : 1);
        $response->assertJsonPath('accepted_terms', $_ENV['DB_CONNECTION'] === 'pgsql' ? true : 1);
    }

    public function testIndexForNonEmpty(): void
    {
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->getJson('/api/teams/' . static::$team->id . '/characters');
        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonCount(1);
    }

    public function testDestroyForFailure(): void
    {
        Event::fake([MemberRemoved::class, TeamUpdated::class]);

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->deleteJson('/api/teams/' . static::$team->id . '/characters/' . static::$team->ledBy->characters->first()->id);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Team doesn't exist
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/1000/characters/' . static::$team->ledBy->characters->first()->id);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team not found!');

        # Case 3: Not a team leader/creator and not the member themselves
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/' . static::$team->id . '/characters/' . static::$team->ledBy->characters->first()->id);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $response->assertJsonPath('message', 'Not allowed to terminate this team membership record! Only the member themselves or the team leader can do that.');

        # Case 4: No such record
        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/' . static::$team->id . '/characters/1000');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Team has no such member!');

        Event::assertNotDispatched(TeamUpdated::class);
        Event::assertNotDispatched(MemberRemoved::class);
    }

    public function testDestroyForSuccess(): void
    {
        Event::fake([MemberRemoved::class, TeamUpdated::class]);

        $response = $this
            ->actingAs(static::$team->ledBy)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/' . static::$team->id . '/characters/' . static::$team->ledBy->characters->first()->id);
        $response->assertStatus(JsonResponse::HTTP_NO_CONTENT);
        $response->assertNoContent();

        static::$team->refresh();
        static::assertEquals(3, static::$team->members->count());
        static::assertEquals(0, static::$team->members->filter(static function (Character $character) {
            return $character->owner->id === static::$team->ledBy->id;
        })->count());

        /** @var Character $character */
        $character = static::$team->members->first();
        $character->loadMissing('owner');
        $response = $this
            ->actingAs($character->owner)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/' . static::$team->id . '/characters/' . $character->id);
        $response->assertStatus(JsonResponse::HTTP_NO_CONTENT);
        $response->assertNoContent();

        static::$team->refresh();
        static::assertEquals(2, static::$team->members->count());
        static::assertEquals(0, static::$team->members->filter(static function (Character $character) {
            return $character->owner->id === static::$team->ledBy->id;
        })->count());

        Event::assertDispatched(TeamUpdated::class);
        Event::assertDispatched(MemberRemoved::class);
    }
}

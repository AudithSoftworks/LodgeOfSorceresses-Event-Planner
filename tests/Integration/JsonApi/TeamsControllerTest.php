<?php

namespace App\Tests\Integration\JsonApi;

use App\Events\Team\TeamDeleted;
use App\Events\Team\TeamUpdated;
use App\Models\Team;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class TeamsControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    /**
     * @var bool
     */
    protected static bool $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate:fresh');
            static::$setupHasRunOnce = true;
        }
    }

    public function testStoreForFailure(): void
    {
        $tierFourMemberUser = $this->stubCustomUserWithCustomCharacters('member', 4, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $tierOneAdminUser = $this->stubCustomUserWithCustomCharacters('admin', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);

        # Case 1: No authentication
        $response = $this
            ->postJson('/api/teams', []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        # Case 1: No authorization
        $response = $this
            ->actingAs($tierFourMemberUser, 'api')
            ->postJson('/api/teams', []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 3: Invalid input
        $response = $this
            ->actingAs($tierOneAdminUser, 'api')
            ->postJson('/api/teams', [
                'tier' => 7,
                'discord_role_id' => 'bogus',
                'led_by' => $tierFourMemberUser,
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(4, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Team name is required.');
        $response->assertJsonPath('errors.tier.0', 'Tier must be from 1 to 4.');
        $response->assertJsonPath('errors.discord_role_id.0', 'Discord Role-ID needs to be a numeric value.');
        $response->assertJsonPath('errors.led_by.0', 'Team Leader needs to be a numeric value.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case 4: Invalid discord_role_id
        $response = $this
            ->actingAs($tierOneAdminUser, 'api')
            ->postJson('/api/teams', [
                'name' => 'Team',
                'tier' => 4,
                'discord_role_id' => '491244517589254446',
                'led_by' => $tierOneAdminUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.discord_role_id.0', 'Discord Role-ID isn\'t valid.');

        # Case 4: Non-eligible team leader
        $response = $this
            ->actingAs($tierOneAdminUser, 'api')
            ->postJson('/api/teams', [
                'name' => 'Team',
                'tier' => 4,
                'discord_role_id' => '499973058401140737',
                'led_by' => $tierOneAdminUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.led_by.0', 'User doesn\'t have an eligible character to join this team.');
    }

    public function testStoreForSuccess(): Team
    {
        $tierFourMemberUser = $this->stubCustomUserWithCustomCharacters('member', 4, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $tierOneAdmin = $this->stubCustomUserWithCustomCharacters('admin', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);

        $response = $this
            ->actingAs($tierOneAdmin, 'api')
            ->withoutMiddleware()
            ->postJson('/api/teams', [
                'name' => 'Core 1',
                'tier' => 4,
                'discord_role_id' => '499973058401140737',
                'led_by' => $tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_CREATED);
        /** @var \App\Models\Team $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated); // Data returned is from cache, thus not new.
        static::assertIsInt($responseOriginalContent->id);

        return $responseOriginalContent;
    }

    public function testShowForFailure(): void
    {
        $response = $this->getJson('/api/teams/1');
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        $guestUser = $this->stubCustomUserWithCustomCharacters();
        $response = $this
            ->actingAs($guestUser, 'api')
            ->getJson('/api/teams/1');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        $tierFourMemberUser = $this->stubCustomUserWithCustomCharacters('member', 4, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $response = $this
            ->actingAs($tierFourMemberUser, 'api')
            ->getJson('/api/teams/10000');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * @depends testStoreForSuccess
     *
     * @param \App\Models\Team $team
     */
    public function testShowForSuccess(Team $team): void
    {
        $tierFourMemberUser = $this->stubCustomUserWithCustomCharacters('member', 4, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $response = $this
            ->actingAs($tierFourMemberUser, 'api')
            ->withoutMiddleware()
            ->getJson(sprintf('/api/teams/%d', $team->id));
        $response->assertStatus(JsonResponse::HTTP_OK);
    }

    /**
     * @depends testStoreForSuccess
     *
     * @param \App\Models\Team $team
     */
    public function testUpdateForFailure(Team $team): void
    {
        Event::fake([TeamUpdated::class]);

        $guestUser = $this->stubCustomUserWithCustomCharacters();

        # Case 1: No authentication
        $response = $this
            ->putJson('/api/teams/1', []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        # Case 1: No authorization
        $response = $this
            ->actingAs($guestUser, 'api')
            ->putJson('/api/teams/1', []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Non-existent team
        $response = $this
            ->actingAs($team->ledBy, 'api')
            ->putJson('/api/teams/100', []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        # Case 3: Invalid input
        $response = $this
            ->actingAs($team->ledBy, 'api')
            ->putJson('/api/teams/1', [
                'discord_role_id' => 'bogus',
                'led_by' => $team->ledBy,
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(2, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.discord_role_id.0', 'Discord Role-ID needs to be a numeric value.');
        $response->assertJsonPath('errors.led_by.0', 'Team Leader needs to be a numeric value.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case 4: Invalid discord_role_id
        $tierOneMember = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $response = $this
            ->actingAs($team->ledBy, 'api')
            ->putJson(sprintf('/api/teams/%d', $team->id), [
                'name' => 'Team',
                'tier' => 4,
                'discord_role_id' => '491244517589254446',
                'led_by' => $tierOneMember->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.discord_role_id.0', 'Discord Role-ID isn\'t valid.');

        # Case 5: Non-eligible team leader
        $tierOneMember = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $response = $this
            ->actingAs($team->ledBy, 'api')
            ->putJson(sprintf('/api/teams/%d', $team->id), [
                'name' => 'Team',
                'tier' => 4,
                'discord_role_id' => '499973058401140737',
                'led_by' => $tierOneMember->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.led_by.0', 'User doesn\'t have an eligible character to join this team.');

        # Case 6: Non-admin trying to edit someone else's team
        $response = $this
            ->actingAs($tierOneMember, 'api')
            ->putJson(sprintf('/api/teams/%d', $team->id), [
                'name' => 'Team',
                'tier' => 4,
                'discord_role_id' => '499973058401140737',
                'led_by' => $team->createdBy->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        Event::assertNotDispatched(TeamUpdated::class);
    }

    /**
     * @depends testStoreForSuccess
     *
     * @param \App\Models\Team $team
     *
     * @return \App\Models\Team
     */
    public function testUpdateForSuccess(Team $team): Team
    {
        Event::fake([TeamUpdated::class]);

        $tierFourMemberUser = $this->stubCustomUserWithCustomCharacters('member', 4, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);

        # Case 1: Team leader their own team
        $response = $this
            ->actingAs($team->ledBy, 'api')
            ->putJson(sprintf('/api/teams/%d', $team->id), [
                'name' => 'Core 2',
                'tier' => 4,
                'discord_role_id' => '499973058401140737',
                'led_by' => $tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Team $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated);
        static::assertEquals('Core 2', $responseOriginalContent->name);

        # Case 2: Admin updating someone else's team
        $response = $this
            ->actingAs($team->createdBy, 'api')
            ->putJson(sprintf('/api/teams/%d', $team->id), [
                'name' => 'Core 3',
                'tier' => 4,
                'discord_role_id' => '499973058401140737',
                'led_by' => $tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Team $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated);
        static::assertEquals('Core 3', $responseOriginalContent->name);

        Event::assertDispatched(TeamUpdated::class);

        return $responseOriginalContent;
    }

    /**
     * @depends testUpdateForSuccess
     *
     * @param \App\Models\Team $team
     */
    public function testDestroyForFailure(Team $team): void
    {
        Event::fake([TeamDeleted::class]);

        $soulshrivenUser = $this->stubCustomUserWithCustomCharacters('soulshriven');

        # Case: No authentication
        $response = $this->deleteJson(sprintf('/api/teams/%d', $team->id));
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        # Case: No authorization - Team leader trying to delete their team
        $response = $this
            ->actingAs($team->ledBy, 'api')
            ->deleteJson(sprintf('/api/teams/%d', $team->id));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        Event::assertNotDispatched(TeamDeleted::class);

        # Case: No authorization - User trying to delete someone else's team
        $response = $this
            ->actingAs($soulshrivenUser, 'api')
            ->deleteJson(sprintf('/api/teams/%d', $team->id));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        Event::assertNotDispatched(TeamDeleted::class);
    }

    /**
     * @depends testUpdateForSuccess
     *
     * @param \App\Models\Team $team
     */
    public function testDestroyForSuccess(Team $team): void
    {
        Event::fake([TeamDeleted::class]);

        $response = $this
            ->actingAs($team->createdBy, 'api')
            ->deleteJson(sprintf('/api/teams/%d', $team->id));
        $response->assertStatus(JsonResponse::HTTP_NO_CONTENT);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertEmpty($responseOriginalContent);

        Event::assertDispatched(TeamDeleted::class);
    }
}

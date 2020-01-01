<?php

namespace App\Tests\Integration\JsonApi;

use App\Events\Team\TeamDeleted;
use App\Events\Team\TeamUpdated;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

class TeamsControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    public static function setUpBeforeClass(): void
    {
        app(ConsoleKernel::class)->call('migrate:refresh');
    }

    public function testStoreForFailure(): void
    {
        $this->stubTierFourMemberUser();
        $tierFourAdmin = $this->stubTierFourAdminUser();
        $tierOneAdmin = $this->stubTierOneAdminUser();

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/teams', [
                'tier' => 7,
                'led_by' => static::$adminUser,
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Non-admin attempting to create a team
        $response = $this
            ->actingAs(static::$tierFourMemberUser)
            ->withoutMiddleware()
            ->postJson('/api/teams', [
                'name' => 'Core 1',
                'tier' => 4,
                'discord_id' => '491244517579254146',
                'led_by' => static::$tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 3: Invalid input
        $response = $this
            ->actingAs($tierFourAdmin)
            ->postJson('/api/teams', [
                'tier' => 7,
                'discord_id' => 'bogus',
                'led_by' => $tierFourAdmin,
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertCount(2, $responseOriginalContent);
        $this->assertCount(4, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Team name is required.');
        $response->assertJsonPath('errors.tier.0', 'Tier must be from 1 to 4.');
        $response->assertJsonPath('errors.discord_id.0', 'The discord id must be a number.');
        $response->assertJsonPath('errors.led_by.0', 'The led by must be a number.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case 4: Non-eligible team leader
        $response = $this
            ->actingAs($tierOneAdmin)
            ->postJson('/api/teams', [
                'name' => 'Team',
                'tier' => 4,
                'discord_id' => '491244517589254446',
                'led_by' => $tierOneAdmin->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertCount(2, $responseOriginalContent);
        $this->assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.led_by.0', 'User doesn\'t have an eligible character to join this team.');
    }

    public function testStoreForSuccess(): void
    {
        $tierOneAdmin = $this->stubTierOneAdminUser();

        $response = $this
            ->actingAs($tierOneAdmin)
            ->withoutMiddleware()
            ->postJson('/api/teams', [
                'name' => 'Core 1',
                'tier' => 4,
                'discord_id' => '491244517579254146',
                'led_by' => static::$tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_CREATED);
        /** @var \App\Models\Team $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertTrue($responseOriginalContent->exists);
        $this->assertTrue($responseOriginalContent->wasRecentlyCreated);
        $this->assertIsInt($responseOriginalContent->id);
    }

    public function testShowForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/teams/1');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        $response = $this
            ->actingAs(static::$tierFourMemberUser)
            ->withoutMiddleware()
            ->getJson('/api/teams/10000');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * @depends testStoreForSuccess
     */
    public function testShowForSuccess(): void
    {
        $response = $this
            ->actingAs(static::$tierFourMemberUser)
            ->withoutMiddleware()
            ->getJson('/api/teams/1');
        $response->assertStatus(200);
    }

    public function testUpdateForFailure(): void
    {
        Event::fake([TeamUpdated::class]);

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->putJson('/api/teams/1', [
                'tier' => 7,
                'led_by' => static::$adminUser,
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: Non-existent team
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/teams/100', [
                'tier' => 7,
                'led_by' => static::$adminUser,
            ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        # Case 3: Invalid input
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/teams/1', [
                'discord_id' => 'bogus',
                'led_by' => static::$adminUser,
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertCount(2, $responseOriginalContent);
        $this->assertCount(4, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Team name is required.');
        $response->assertJsonPath('errors.tier.0', 'Choose a tier for the content this team is specifialized in.');
        $response->assertJsonPath('errors.discord_id.0', 'The discord id must be a number.');
        $response->assertJsonPath('errors.led_by.0', 'The led by must be a number.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case 4: Non-eligible team leader
        $tierOneMember = $this->stubTierXMemberUser(1);
        $response = $this
            ->actingAs(static::$adminUser)
            ->postJson('/api/teams', [
                'name' => 'Team',
                'tier' => 4,
                'discord_id' => '491244517589254446',
                'led_by' => $tierOneMember->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertCount(2, $responseOriginalContent);
        $this->assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.led_by.0', 'User doesn\'t have an eligible character to join this team.');

        # Case 5: Non-admin trying to edit someone else's team
        $response = $this
            ->actingAs($tierOneMember)
            ->postJson('/api/teams', [
                'name' => 'Team',
                'tier' => 4,
                'discord_id' => '491244517589254446',
                'led_by' => static::$adminUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        Event::assertNotDispatched(TeamUpdated::class);
    }

    /**
     * @depends testStoreForSuccess
     */
    public function testUpdateForSuccess(): void
    {
        Event::fake([TeamUpdated::class]);

        # Case 1: Non-admin updating their own team
        $response = $this
            ->actingAs(static::$tierFourMemberUser)
            ->withoutMiddleware()
            ->putJson('/api/teams/1', [
                'name' => 'Core 2',
                'tier' => 4,
                'discord_id' => '491244517589254146',
                'led_by' => static::$tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Team $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertTrue($responseOriginalContent->exists);
        $this->assertFalse($responseOriginalContent->wasRecentlyCreated);
        $this->assertEquals('Core 2', $responseOriginalContent->name);

        # Case 2: Admin updating someone else's team
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/teams/1', [
                'name' => 'Core 3',
                'tier' => 4,
                'discord_id' => '491244517589254146',
                'led_by' => static::$tierFourMemberUser->id,
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Team $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertTrue($responseOriginalContent->exists);
        $this->assertFalse($responseOriginalContent->wasRecentlyCreated);
        $this->assertEquals('Core 3', $responseOriginalContent->name);

        Event::assertDispatched(TeamUpdated::class);
    }

    public function testDestroyForFailure(): void
    {
        Event::fake([TeamDeleted::class]);

        $this->stubSoulshrivenUser();

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->deleteJson('/api/teams/1');
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: User trying to delete someone else's team
        $response = $this
            ->actingAs(static::$soulshriven)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/1');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        Event::assertNotDispatched(TeamDeleted::class);
    }

    /**
     * @depends testUpdateForSuccess
     */
    public function testDestroyForSuccess(): void
    {
        Event::fake([TeamDeleted::class]);
        $this->stubTierFourMemberUser();

        $response = $this
            ->actingAs(static::$tierFourMemberUser)
            ->withoutMiddleware()
            ->deleteJson('/api/teams/1');
        $response->assertStatus(JsonResponse::HTTP_NO_CONTENT);
        $responseOriginalContent = $response->getOriginalContent();
        $this->assertEmpty($responseOriginalContent);

        Event::assertDispatched(TeamDeleted::class);
    }
}

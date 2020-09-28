<?php

namespace App\Tests\Integration\JsonApi\Admin;

use App\Events\Character\CharacterDemoted;
use App\Events\Character\CharacterPromoted;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class CharactersControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    /**
     * @var bool
     */
    protected static bool $setupHasRunOnce = false;

    protected static ?User $adminUser;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate:fresh');
            static::$setupHasRunOnce = true;

            static::$adminUser = $this->stubCustomUserWithCustomCharacters('admin', 3, RoleTypes::ROLE_TANK, ClassTypes::CLASS_NECROMANCER);
        }
    }

    public function testIndexForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/admin/characters');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testShowForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->getJson('/api/admin/characters/1');
        $response->assertStatus(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testStoreForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/admin/characters', []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testUpdateForFailure(): void
    {
        # Case: No authentication.
        $response = $this
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/1', []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotNull($responseOriginalContent);
        $response->assertJsonPath('message', 'This action is unauthorized.');

        # Case: Not found.
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/1000', [
                'action' => 'promote'
            ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(1, $responseOriginalContent);
        $response->assertJsonPath('message', 'Character not found!');

        # Case: Self-ranking.
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/' . static::$adminUser->characters->first()->id, [
                'action' => 'promote'
            ]);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(1, $responseOriginalContent);
        $response->assertJsonPath('message', 'Self-ranking disabled!');

        # Case: Attempting to rerank a Damage-Dealer
        $tierTwoDdUser = $this->stubCustomUserWithCustomCharacters('member', 2, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/' . $tierTwoDdUser->characters->first()->id, [
                'action' => 'promote'
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.action.0', 'Damage Dealers can only be ranked via Parse submission!');

        # Case: Missing 'action' parameter.
        $tierTwoHealerUser = $this->stubCustomUserWithCustomCharacters('member', 2, RoleTypes::ROLE_HEALER, ClassTypes::CLASS_TEMPLAR);
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/' . $tierTwoHealerUser->characters->first()->id, []);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.action.0', 'Action is required.');

        # Case: Missing 'action' parameter.
        $tierTwoHealerUser = $this->stubCustomUserWithCustomCharacters('member', 2, RoleTypes::ROLE_HEALER, ClassTypes::CLASS_TEMPLAR);
        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/' . $tierTwoHealerUser->characters->first()->id, [
                'action' => 'random-value'
            ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(1, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.action.0', 'Action should either be promote or demote.');
    }

    public function testUpdateForSuccessForActionPromote(): void
    {
        Event::fake([CharacterPromoted::class, CharacterDemoted::class]);

        $tierTwoTankUser = $this->stubCustomUserWithCustomCharacters('member', 2, RoleTypes::ROLE_TANK, ClassTypes::CLASS_DRAGONKNIGHT);

        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/' . $tierTwoTankUser->characters->first()->id, [
                'action' => 'promote'
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Character $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated);
        static::assertEquals(3, $responseOriginalContent->approved_for_tier);

        Event::assertDispatched(CharacterPromoted::class);
        Event::assertNotDispatched(CharacterDemoted::class);
    }

    public function testUpdateForSuccessForActionDemote(): void
    {
        Event::fake([CharacterPromoted::class, CharacterDemoted::class]);

        $tierTwoHealerUser = $this->stubCustomUserWithCustomCharacters('member', 2, RoleTypes::ROLE_HEALER, ClassTypes::CLASS_TEMPLAR);

        $response = $this
            ->actingAs(static::$adminUser)
            ->withoutMiddleware()
            ->putJson('/api/admin/characters/' . $tierTwoHealerUser->characters->first()->id, [
                'action' => 'demote'
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Character $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated);
        static::assertEquals(1, $responseOriginalContent->approved_for_tier);

        Event::assertDispatched(CharacterDemoted::class);
        Event::assertNotDispatched(CharacterPromoted::class);
    }

    public function testDestroyForFailure(): void
    {
        $response = $this
            ->withoutMiddleware()
            ->deleteJson('/api/admin/characters/1');
        $response->assertStatus(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }
}

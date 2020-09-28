<?php

namespace App\Tests\Integration\JsonApi\Auth;

use App\Events\Character\CharacterDeleted;
use App\Events\Character\CharacterSaved;
use App\Models\Character;
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

    protected static bool $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate:fresh');
            Artisan::call('pmg:sets');
            Artisan::call('pmg:skills');
            Artisan::call('db:seed');
            static::$setupHasRunOnce = true;
        }
    }

    public function testStoringForFailure(): void
    {
        $guestUser = $this->stubCustomUserWithCustomCharacters();
        $tierOneUser = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);

        Event::fake([CharacterSaved::class]);

        # Case: No authentication
        $response = $this->postJson('/api/users/@me/characters', []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        # Case: No permission
        $response = $this
            ->actingAs($guestUser, 'api')
            ->postJson('/api/users/@me/characters', []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case: Invalid input
        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->postJson('/api/users/@me/characters', []);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(4, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Character name is required.');
        $response->assertJsonPath('errors.role.0', 'Choose a role.');
        $response->assertJsonPath('errors.class.0', 'Choose a class.');
        $response->assertJsonFragment(['sets' => [0 => 'Select all full sets your Character has.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case: Invalid input 2
        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->postJson('/api/users/@me/characters', [
                'name' => 1,
                'role' => 'bogus-id',
                'class' => 'bogus-id',
                'content' => [0, 'a'],
                'sets' => [0, 'a'],
                'skills' => [0, 'a'],
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(9, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Character name must be string.');
        $response->assertJsonFragment([
            'role' => [
                0 => 'Role must be an integer.',
                1 => 'Role must be an integer between 1 and 4.',
            ],
        ]);
        $response->assertJsonFragment([
            'class' => [
                0 => 'Class must be an integer.',
                1 => 'Class must be an integer between 1 and 6.',
            ],
        ]);
        $response->assertJsonFragment(['sets.0' => [0 => 'One or more of given sets doesn\'t exist.']]);
        $response->assertJsonFragment(['sets.1' => [0 => 'Set must be an integer.']]);
        $response->assertJsonFragment(['skills.0' => [0 => 'One or more of given skills doesn\'t exist.']]);
        $response->assertJsonFragment(['skills.1' => [0 => 'Skill must be an integer.']]);
        $response->assertJsonFragment(['content.0' => [0 => 'One or more of given content doesn\'t exist.']]);
        $response->assertJsonFragment(['content.1' => [0 => 'Content must be an integer.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case: Invalid input 3
        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->postJson('/api/users/@me/characters', [
                'name' => 1,
                'role' => null,
                'class' => null,
                'content' => 5,
                'sets' => 5,
                'skills' => 11,
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(6, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Character name must be string.');
        $response->assertJsonPath('errors.role.0', 'Choose a role.');
        $response->assertJsonPath('errors.class.0', 'Choose a class.');
        $response->assertJsonPath('errors.sets.0', 'Sets must be an array of integers.');
        $response->assertJsonPath('errors.skills.0', 'Skills must be an array of integers.');
        $response->assertJsonPath('errors.content.0', 'Content must be an array of integers.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        Event::assertNotDispatched(CharacterSaved::class);
    }

    public function testStoringForSuccess(): Character
    {
        Event::fake([CharacterSaved::class]);

        $tierOneUser = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);

        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->withoutMiddleware()
            ->postJson('/api/users/@me/characters', [
                'name' => 'Some Character',
                'role' => RoleTypes::ROLE_MAGICKA_DD,
                'class' => ClassTypes::CLASS_TEMPLAR,
                'content' => [1],
                'sets' => [5, 6],
                'skills' => [9],
            ]);
        $response->assertStatus(JsonResponse::HTTP_CREATED);
        /** @var \App\Models\Character $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertInstanceOf(Character::class, $responseOriginalContent);
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated); // Data returned is from cache, thus not new.
        static::assertIsInt($responseOriginalContent->id);

        static::assertEquals('Some Character', $responseOriginalContent->name);
        static::assertEquals(RoleTypes::getShortRoleText(RoleTypes::ROLE_MAGICKA_DD), $responseOriginalContent->role);
        static::assertEquals(ClassTypes::getClassName(ClassTypes::CLASS_TEMPLAR), $responseOriginalContent->class);
        static::assertCount(1, $responseOriginalContent->content);
        static::assertCount(2, $responseOriginalContent->sets);
        static::assertCount(1, $responseOriginalContent->skills);

        Event::assertDispatched(CharacterSaved::class);

        return $responseOriginalContent;
    }

    /**
     * @depends testStoringForSuccess
     *
     * @param \App\Models\Character $character
     */
    public function testUpdatingForFailure(Character $character): void
    {
        $guestUser = $this->stubCustomUserWithCustomCharacters();
        $tierOneUser = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);

        Event::fake([CharacterSaved::class]);

        # Case: No authentication
        $response = $this
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        # Case: No permission
        $response = $this
            ->actingAs($guestUser, 'api')
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case: Invalid owner = Not found
        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), [
                'name' => 'New Character',
                'role' => RoleTypes::ROLE_MAGICKA_DD,
                'class' => ClassTypes::CLASS_TEMPLAR,
                'content' => [1, 2],
                'sets' => [7],
                'skills' => [12, 13],
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(1, $responseOriginalContent);
        $response->assertJsonPath('message', 'Character not found!');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        # Case: Invalid input
        $owner = $character->owner;
        $response = $this
            ->actingAs($owner, 'api')
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), [
                'name' => null,
                'role' => 'bogus-id',
                'class' => 99,
                'content' => 'a',
                'sets' => 5,
                'skills' => [0, 'a'],
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(7, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Character name is required.');
        $response->assertJsonFragment([
            'role' => [
                0 => 'Role must be an integer.',
                1 => 'Role must be an integer between 1 and 4.',
            ],
        ]);
        $response->assertJsonPath('errors.class.0', 'Class must be an integer between 1 and 6.');
        $response->assertJsonPath('errors.sets.0', 'Sets must be an array of integers.');
        $response->assertJsonFragment(['skills.0' => [0 => 'One or more of given skills doesn\'t exist.']]);
        $response->assertJsonFragment(['skills.1' => [0 => 'Skill must be an integer.']]);
        $response->assertJsonPath('errors.content.0', 'Content must be an array of integers.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case: Invalid input 2
        $response = $this
            ->actingAs($owner, 'api')
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), [
                'name' => 1,
                'role' => null,
                'class' => null,
                'content' => [0, 'a'],
                'sets' => [0, 'a'],
                'skills' => 11,
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(8, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.name.0', 'Character name must be string.');
        $response->assertJsonPath('errors.role.0', 'Choose a role.');
        $response->assertJsonPath('errors.class.0', 'Choose a class.');
        $response->assertJsonFragment(['sets.0' => [0 => 'One or more of given sets doesn\'t exist.']]);
        $response->assertJsonFragment(['sets.1' => [0 => 'Set must be an integer.']]);
        $response->assertJsonPath('errors.skills.0', 'Skills must be an array of integers.');
        $response->assertJsonFragment(['content.0' => [0 => 'One or more of given content doesn\'t exist.']]);
        $response->assertJsonFragment(['content.1' => [0 => 'Content must be an integer.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        Event::assertNotDispatched(CharacterSaved::class);
    }

    /**
     * @depends testStoringForSuccess
     *
     * @param \App\Models\Character $character
     *
     * @return \App\Models\Character
     */
    public function testUpdatingNonTieredCharacter(Character $character): Character
    {
        Event::fake([CharacterSaved::class]);

        $response = $this
            ->actingAs($character->owner, 'api')
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), [
                'name' => 'New Character',
                'role' => RoleTypes::ROLE_HEALER,
                'class' => ClassTypes::CLASS_SORCERER,
                'content' => [2, 3],
                'sets' => [5, 6, 7],
                'skills' => [5, 13],
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Character $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertInstanceOf(Character::class, $responseOriginalContent);
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated); // Data returned is from cache, thus not new.
        static::assertIsInt($responseOriginalContent->id);

        static::assertEquals('New Character', $responseOriginalContent->name);
        static::assertEquals(RoleTypes::getShortRoleText(RoleTypes::ROLE_HEALER), $responseOriginalContent->role);
        static::assertEquals(ClassTypes::getClassName(ClassTypes::CLASS_SORCERER), $responseOriginalContent->class);
        static::assertCount(2, $responseOriginalContent->content);
        static::assertCount(3, $responseOriginalContent->sets);
        static::assertCount(2, $responseOriginalContent->skills);

        Event::assertDispatched(CharacterSaved::class);

        return $responseOriginalContent;
    }

    /**
     * @depends testUpdatingNonTieredCharacter
     *
     * @param \App\Models\Character $character
     *
     * @return \App\Models\Character
     */
    public function testUpdatingTieredCharacter(Character $character): Character
    {
        # Modify data for the next test.
        $characterRaw = Character::query()->find($character->id);
        $characterRaw->approved_for_tier = 2;
        $characterRaw->save();

        Event::fake([CharacterSaved::class]);

        # Case: Attempting to update Tiered Character
        $response = $this
            ->actingAs($character->owner, 'api')
            ->putJson(sprintf('/api/users/@me/characters/%d', $character->id), [
                'role' => RoleTypes::ROLE_TANK,
                'class' => ClassTypes::CLASS_NECROMANCER,
                'sets' => [5, 6, 7],
            ]);
        $response->assertStatus(JsonResponse::HTTP_OK);
        /** @var \App\Models\Character $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertInstanceOf(Character::class, $responseOriginalContent);
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated); // Data returned is from cache, thus not new.
        static::assertIsInt($responseOriginalContent->id);

        static::assertEquals(RoleTypes::getShortRoleText(RoleTypes::ROLE_HEALER), $responseOriginalContent->role);
        static::assertEquals(ClassTypes::getClassName(ClassTypes::CLASS_SORCERER), $responseOriginalContent->class);

        Event::assertDispatched(CharacterSaved::class);

        return $responseOriginalContent;
    }

    /**
     * @depends testUpdatingTieredCharacter
     *
     * @param \App\Models\Character $character
     */
    public function testDeletingForFailure(Character $character): void
    {
        $guestUser = $this->stubCustomUserWithCustomCharacters();
        $soulshrivenUser = $this->stubCustomUserWithCustomCharacters('soulshriven');

        Event::fake([CharacterDeleted::class]);

        # Case 1: No authentication
        $response = $this
            ->deleteJson(sprintf('/api/users/@me/characters/%d', $character->id));
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);

        # Case 1: No authorization
        $response = $this
            ->actingAs($guestUser, 'api')
            ->deleteJson(sprintf('/api/users/@me/characters/%d', $character->id));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: User trying to delete someone else's Parse
        $response = $this
            ->actingAs($soulshrivenUser, 'api')
            ->deleteJson(sprintf('/api/users/@me/characters/%d', $character->id));
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        Event::assertNotDispatched(CharacterDeleted::class);
    }

    /**
     * @depends testUpdatingTieredCharacter
     *
     * @param \App\Models\Character $character
     */
    public function testDeletingTieredCharacter(Character $character): void
    {
        Event::fake([CharacterDeleted::class]);

        # Case: Attempt to delete non-Tier-0
        $response = $this
            ->actingAs($character->owner, 'api')
            ->deleteJson(sprintf('/api/users/@me/characters/%d', $character->id));
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertNotEmpty($responseOriginalContent);
        $response->assertJsonPath('message', 'Character not found!');

        Event::assertNotDispatched(CharacterDeleted::class);
    }

    /**
     * @depends testUpdatingTieredCharacter
     *
     * @param \App\Models\Character $character
     *
     * @return \App\Models\Character
     */
    public function testDeletingNonTieredCharacterBelongingToWrongOwner(Character $character): Character
    {
        $characterRaw = Character::query()->find($character->id);
        $characterRaw->approved_for_tier = 0;
        $characterRaw->save();

        $guestUser = $this->stubCustomUserWithCustomCharacters();

        Event::fake([CharacterDeleted::class]);

        $response = $this
            ->actingAs($guestUser, 'api')
            ->deleteJson(sprintf('/api/users/@me/characters/%d', $character->id));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        Event::assertNotDispatched(CharacterDeleted::class);

        return $character;
    }

    /**
     * @depends testDeletingNonTieredCharacterBelongingToWrongOwner
     *
     * @param \App\Models\Character $character
     */
    public function testDeletingNonTieredCharacterWithCorrectOwner(Character $character): void
    {
        Event::fake([CharacterDeleted::class]);

        $response = $this
            ->actingAs($character->owner, 'api')
            ->deleteJson(sprintf('/api/users/@me/characters/%d', $character->id));
        $response->assertStatus(JsonResponse::HTTP_NO_CONTENT);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertEmpty($responseOriginalContent);

        Event::assertDispatched(CharacterDeleted::class);
    }
}

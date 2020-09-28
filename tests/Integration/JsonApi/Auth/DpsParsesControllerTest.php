<?php

namespace App\Tests\Integration\JsonApi\Auth;

use App\Events\DpsParse\DpsParseDeleted;
use App\Events\DpsParse\DpsParseSubmitted;
use App\Models\Character;
use App\Models\File;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Tests\IlluminateTestCase;
use App\Tests\Integration\JsonApi\Traits\NeedsUserStubs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class DpsParsesControllerTest extends IlluminateTestCase
{
    use NeedsUserStubs;

    protected static bool $setupHasRunOnce = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setupHasRunOnce) {
            Artisan::call('migrate:fresh');
            Artisan::call('pmg:sets');
            static::$setupHasRunOnce = true;
        }
    }

    public function testStoreForFailure(): void
    {
        Event::fake([DpsParseSubmitted::class]);

        $tierOneUser = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        /** @var \App\Models\Character $tierOneUsersCharacter */
        $tierOneUsersCharacter = $tierOneUser->characters->first();

        # Case: No authentication
        $response = $this
            ->withoutMiddleware()
            ->postJson(sprintf('/api/users/@me/characters/%d/parses', $tierOneUsersCharacter->id), []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case: Invalid input
        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->postJson(sprintf('/api/users/@me/characters/%d/parses', $tierOneUsersCharacter->id), []);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(4, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonPath('errors.parse_file_hash.0', 'CMX Combat screen screenshot needs to be uploaded.');
        $response->assertJsonPath('errors.info_file_hash.0', 'CMX Info screen screenshot needs to be uploaded.');
        $response->assertJsonPath('errors.dps_amount.0', 'DPS Number is required.');
        $response->assertJsonPath('errors.sets.0', 'Provide the list of Sets worn during Parse.');
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        # Case: Invalid input 2
        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->postJson(sprintf('/api/users/@me/characters/%d/parses', $tierOneUsersCharacter->id), [
                'sets' => [0],
                'dps_amount' => 'string',
                'parse_file_hash' => 'bogus-hash',
                'info_file_hash' => 'bogus-hash',
            ]);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertCount(2, $responseOriginalContent);
        static::assertCount(5, $responseOriginalContent['errors']);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonFragment(['parse_file_hash' => [0 => 'CMX Combat screen screenshot file not found.']]);
        $response->assertJsonFragment(['info_file_hash' => [0 => 'CMX Info screen screenshot file not found.']]);
        $response->assertJsonFragment(['dps_amount' => [0 => 'The dps amount must be a number.']]);
        $response->assertJsonFragment(['sets' => [0 => 'Number of sets worn during Parse should be between 2 and 5.']]);
        $response->assertJsonFragment(['sets.0' => [0 => 'One or more invalid Sets provided.']]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        Event::assertNotDispatched(DpsParseSubmitted::class);
    }

    public function testStoreForSuccess(): Character
    {
        Event::fake([DpsParseSubmitted::class]);

        $tierOneUser = $this->stubCustomUserWithCustomCharacters('member', 1, RoleTypes::ROLE_MAGICKA_DD, ClassTypes::CLASS_SORCERER);
        /** @var File $file */
        $file = File::factory()->create();
        $file->uploaders()->attach([
            $tierOneUser->id => [
                'qquuid' => 'qquid',
                'original_client_name' => 'original_client_name',
                'tag' => 'tag'
            ]
        ]);
        /** @var \App\Models\Character $tierOneUsersCharacter */
        $tierOneUsersCharacter = $tierOneUser->characters->first();

        $response = $this
            ->actingAs($tierOneUser, 'api')
            ->withoutMiddleware()
            ->postJson(sprintf('/api/users/@me/characters/%d/parses', $tierOneUsersCharacter->id), [
                'sets' => [2, 3],
                'dps_amount' => 1,
                'parse_file_hash' => $file->hash,
                'info_file_hash' => $file->hash,
            ]);
        $response->assertStatus(JsonResponse::HTTP_CREATED);
        /** @var \App\Models\Character $responseOriginalContent */
        $responseOriginalContent = $response->getOriginalContent();
        static::assertInstanceOf(Character::class, $responseOriginalContent);
        static::assertTrue($responseOriginalContent->exists);
        static::assertFalse($responseOriginalContent->wasRecentlyCreated); // Data returned is from cache, thus not new.
        static::assertIsInt($responseOriginalContent->id);

        Event::assertDispatched(DpsParseSubmitted::class);

        return $responseOriginalContent;
    }

    /**
     * @depends testStoreForSuccess
     *
     * @param \App\Models\Character $character
     */
    public function testDestroyForFailure(Character $character): void
    {
        Event::fake([DpsParseDeleted::class]);

        $soulshrivenUser = $this->stubCustomUserWithCustomCharacters('soulshriven');

        # Case 1: No authentication
        $response = $this
            ->withoutMiddleware()
            ->deleteJson(
                sprintf('/api/users/@me/characters/%d/parses/%d',
                    $character->id,
                    $character->dps_parses_pending->first()->id
                )

            );
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN);

        # Case 2: User trying to delete someone else's Parse
        $response = $this
            ->actingAs($soulshrivenUser, 'api')
            ->withoutMiddleware()
            ->deleteJson(
                sprintf('/api/users/@me/characters/%d/parses/%d',
                    $character->id,
                    $character->dps_parses_pending->first()->id
                )
            );
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        Event::assertNotDispatched(DpsParseDeleted::class);
    }

    /**
     * @depends testStoreForSuccess
     *
     * @param \App\Models\Character $character
     */
    public function testDestroyForSuccess(Character $character): void
    {
        Event::fake([DpsParseDeleted::class]);

        $response = $this
            ->actingAs($character->owner, 'api')
            ->deleteJson(
                sprintf('/api/users/@me/characters/%d/parses/%d',
                    $character->id,
                    $character->dps_parses_pending->first()->id
                )
            );
        $response->assertStatus(JsonResponse::HTTP_NO_CONTENT);
        $responseOriginalContent = $response->getOriginalContent();
        static::assertEmpty($responseOriginalContent);

        Event::assertDispatched(DpsParseDeleted::class);
    }
}

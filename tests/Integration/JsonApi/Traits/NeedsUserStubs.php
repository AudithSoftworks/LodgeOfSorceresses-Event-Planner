<?php

namespace App\Tests\Integration\JsonApi\Traits;

use App\Models\Character;
use App\Models\User;
use App\Models\UserOAuth;

trait NeedsUserStubs
{
    private function stubCustomUserWithCustomCharacters(
        ?string $factoryState = null,
        ?int $tier = null,
        ?int $role = null,
        ?int $class = null
    ): User {
        /** @var \Database\Factories\UserOAuthFactory $userOauthFactory */
        $userOauthFactory = UserOAuth::factory();
        $factoryState !== null && $userOauthFactory = $userOauthFactory->{$factoryState}();

        /** @var \Database\Factories\UserFactory $userFactory */
        $userFactory = User::factory();
        $factoryState !== null && $userFactory = $userFactory->{$factoryState}();

        /** @var \Database\Factories\CharacterFactory $characterFactory */
        $characterFactory = Character::factory();

        /** @var UserOAuth $userOAuth */
        $userOAuth = $userOauthFactory->create([
            'user_id' => $userFactory->create(),
        ]);
        /** @var \App\Models\User $tierXUser */
        $tierXUser = $userOAuth->owner()->first();
        if ($tier !== null && $role !== null && $class !== null) {
            $tierXCharacter = $characterFactory->tier($tier)->role($role, $class)->create([
                'user_id' => $tierXUser,
            ]);
            $tierXUser->setRelation('characters', collect([$tierXCharacter]));
            $tierXUser->save();
        }

        return $tierXUser;
    }
}

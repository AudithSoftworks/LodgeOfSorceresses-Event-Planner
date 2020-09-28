<?php

namespace App\Tests\Integration\JsonApi\Traits;

use App\Models\Character;
use App\Models\User;
use App\Models\UserOAuth;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;

trait NeedsUserStubs
{
    protected static ?User $adminUser = null;

    protected static ?User $tierFourMemberUser = null;

    protected static ?User $soulshriven = null;

    private function stubTierOneAdminUser(): User
    {
        /** @var \Database\Factories\UserOAuthFactory $userOauthFactory */
        $userOauthFactory = UserOAuth::factory();

        /** @var \Database\Factories\UserFactory $userFactory */
        $userFactory = User::factory();

        /** @var \Database\Factories\CharacterFactory $characterFactory */
        $characterFactory = Character::factory();

        /** @var UserOAuth $adminUserOauth */
        $adminUserOauth = $userOauthFactory->admin()->create([
            'user_id' => $userFactory->admin()->create(),
        ]);
        /** @var \App\Models\User $adminUser */
        $adminUser = $adminUserOauth->owner()->first();
        $tierFourCharacter = $characterFactory->tier(1)->create([
            'user_id' => $adminUser,
        ]);
        $adminUser->setRelation('characters', collect([$tierFourCharacter]));
        $adminUser->save();

        return $adminUser;
    }

    private function stubTierFourAdminUser(): User
    {
        if (static::$adminUser) {
            return static::$adminUser;
        }

        /** @var \Database\Factories\UserOAuthFactory $userOauthFactory */
        $userOauthFactory = UserOAuth::factory();

        /** @var \Database\Factories\UserFactory $userFactory */
        $userFactory = User::factory();

        /** @var \Database\Factories\CharacterFactory $characterFactory */
        $characterFactory = Character::factory();

        /** @var UserOAuth $adminUserOauth */
        $adminUserOauth = $userOauthFactory->admin()->create([
            'user_id' => $userFactory->admin()->create(),
        ]);
        /** @var \App\Models\User $adminUser */
        $adminUser = $adminUserOauth->owner()->first();
        $tierFourCharacter = $characterFactory->tier(4)->create([
            'user_id' => $adminUser,
        ]);
        $adminUser->setRelation('characters', collect([$tierFourCharacter]));
        $adminUser->save();

        static::$adminUser = $adminUser;

        return $adminUser;
    }

    private function stubCustomUserWithCustomCharacters(
        ?int $tier = null,
        ?int $role = RoleTypes::ROLE_MAGICKA_DD,
        ?int $class = ClassTypes::CLASS_SORCERER,
        ?string $factoryState = 'member'
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

    private function stubTierFourMemberUser(): void
    {
        if (!static::$tierFourMemberUser) {
            static::$tierFourMemberUser = $this->stubCustomUserWithCustomCharacters(4);
        }
    }

    private function stubSoulshrivenUser(): void
    {
        if (!static::$soulshriven) {
            /** @var \Database\Factories\UserOAuthFactory $userOauthFactory */
            $userOauthFactory = UserOAuth::factory();

            /** @var \Database\Factories\UserFactory $userFactory */
            $userFactory = User::factory();

            /** @var UserOAuth $soulshrivenOauth */
            $soulshrivenOauth = $userOauthFactory->soulshriven()->create([
                'user_id' => $userFactory->soulshriven()->create(),
            ]);
            static::$soulshriven = $soulshrivenOauth->owner;
        }
    }

    private function stubGuestUser(): User
    {
        /** @var \Database\Factories\UserOAuthFactory $userOauthFactory */
        $userOauthFactory = UserOAuth::factory();

        /** @var \Database\Factories\UserFactory $userFactory */
        $userFactory = User::factory();

        /** @var UserOAuth $guestOauth */
        $guestOauth = $userOauthFactory->create([
            'user_id' => $userFactory->create(),
        ]);
        /** @var User $guestUser */
        $guestUser = $guestOauth->owner()->first();

        return $guestUser;
    }
}

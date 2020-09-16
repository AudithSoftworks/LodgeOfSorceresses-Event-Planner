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

    private function stubCustomMemberUserWithCustomCharacters(int $tier, int $role = RoleTypes::ROLE_MAGICKA_DD, int $class = ClassTypes::CLASS_SORCERER): User
    {
        /** @var \Database\Factories\UserOAuthFactory $userOauthFactory */
        $userOauthFactory = UserOAuth::factory();

        /** @var \Database\Factories\UserFactory $userFactory */
        $userFactory = User::factory();

        /** @var \Database\Factories\CharacterFactory $characterFactory */
        $characterFactory = Character::factory();

        /** @var UserOAuth $memberUserOauth */
        $memberUserOauth = $userOauthFactory->member()->create([
            'user_id' => $userFactory->member()->create(),
        ]);
        /** @var \App\Models\User $tierXMemberUser */
        $tierXMemberUser = $memberUserOauth->owner()->first();
        $tierXCharacter = $characterFactory->tier($tier)->role($role, $class)->create([
            'user_id' => $tierXMemberUser,
        ]);
        $tierXMemberUser->setRelation('characters', collect([$tierXCharacter]));
        $tierXMemberUser->save();

        return $tierXMemberUser;
    }

    private function stubTierFourMemberUser(): void
    {
        if (!static::$tierFourMemberUser) {
            static::$tierFourMemberUser = $this->stubCustomMemberUserWithCustomCharacters(4);
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
            /** @var User $soulshrivenUser */
            $soulshrivenUser = $soulshrivenOauth->owner()->first();
            static::$soulshriven = $soulshrivenUser;
        }
    }
}

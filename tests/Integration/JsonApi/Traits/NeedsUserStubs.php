<?php

namespace App\Tests\Integration\JsonApi\Traits;

use App\Models\Character;
use App\Models\User;
use App\Models\UserOAuth;

trait NeedsUserStubs
{
    /**
     * @var \App\Models\User
     */
    protected static $adminUser;

    /**
     * @var \App\Models\User
     */
    protected static $tierFourMemberUser;

    /**
     * @var \App\Models\User
     */
    protected static $soulshriven;

    private function stubTierOneAdminUser(): User
    {
        /** @var UserOAuth $adminUserOauth */
        $adminUserOauth = factory(UserOAuth::class)->states('admin-member')->create([
            'user_id' => factory(User::class)->states('admin-member')->create()
        ]);
        /** @var \App\Models\User $adminUser */
        $adminUser = $adminUserOauth->owner()->first();
        $tierFourCharacter = factory(Character::class)->states('tier-1')->create([
            'user_id' => $adminUser
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

        /** @var UserOAuth $adminUserOauth */
        $adminUserOauth = factory(UserOAuth::class)->states('admin-member')->create([
            'user_id' => factory(User::class)->states('admin-member')->create()
        ]);
        /** @var \App\Models\User $adminUser */
        $adminUser = $adminUserOauth->owner()->first();
        $tierFourCharacter = factory(Character::class)->states('tier-4')->create([
            'user_id' => $adminUser
        ]);
        $adminUser->setRelation('characters', collect([$tierFourCharacter]));
        $adminUser->save();

        static::$adminUser = $adminUser;

        return $adminUser;
    }

    private function stubTierXMemberUser(int $tier): User
    {
        /** @var UserOAuth $memberUserOauth */
        $memberUserOauth = factory(UserOAuth::class)->states('member')->create([
            'user_id' => factory(User::class)->states('member')->create()
        ]);
        /** @var \App\Models\User $tierXMemberUser */
        $tierXMemberUser = $memberUserOauth->owner()->first();
        $tierXCharacter = factory(Character::class)->states('tier-' . $tier)->create([
            'user_id' => $tierXMemberUser
        ]);
        $tierXMemberUser->setRelation('characters', collect([$tierXCharacter]));
        $tierXMemberUser->save();

        return $tierXMemberUser;
    }

    private function stubTierFourMemberUser(): void
    {
        if (!static::$tierFourMemberUser) {
            static::$tierFourMemberUser = $this->stubTierXMemberUser(4);
        }
    }

    private function stubSoulshrivenUser(): void
    {
        if (!static::$soulshriven) {
            /** @var UserOAuth $soulshrivenOauth */
            $soulshrivenOauth = factory(UserOAuth::class)->states('soulshriven')->create([
                'user_id' => factory(User::class)->states('soulshriven')->create()
            ]);
            static::$soulshriven = $soulshrivenOauth->owner()->first();
        }
    }
}

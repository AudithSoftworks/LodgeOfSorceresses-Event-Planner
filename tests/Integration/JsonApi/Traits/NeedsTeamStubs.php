<?php

namespace App\Tests\Integration\JsonApi\Traits;

use App\Models\Character;
use App\Models\Team;
use App\Models\User;
use App\Models\UserOAuth;

trait NeedsTeamStubs
{

    private function stubTierXAdminUserTeam(int $tier): Team
    {
        /** @var UserOAuth $adminUserOauth */
        $adminUserOauth = factory(UserOAuth::class)->states('admin-member')->create([
            'user_id' => factory(User::class)->states('admin-member')->create()
        ]);
        /** @var \App\Models\User $adminUser */
        $adminUser = $adminUserOauth->owner()->first();
        $tierXCharacter = factory(Character::class)->states('tier-' . $tier)->create([
            'user_id' => $adminUser
        ]);
        $adminUser->setRelation('characters', collect([$tierXCharacter]));
        $adminUser->save();

        return factory(Team::class)->create([
            'tier' => $tier,
            'led_by' => $adminUser->id,
            'created_by' => $adminUser->id,
        ]);
    }
}

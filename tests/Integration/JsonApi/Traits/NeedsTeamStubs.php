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
        /** @var \Database\Factories\UserOAuthFactory $userOAuthFactory */
        $userOAuthFactory = UserOAuth::factory();

        /** @var \Database\Factories\UserFactory $userFactory */
        $userFactory = User::factory();

        /** @var \Database\Factories\CharacterFactory $characterFactory */
        $characterFactory = Character::factory();

        /** @var UserOAuth $adminUserOauth */
        $adminUserOauth = $userOAuthFactory->admin()->create([
            'user_id' => $userFactory->admin()->create()
        ]);
        /** @var \App\Models\User $adminUser */
        $adminUser = $adminUserOauth->owner()->first();
        $tierXCharacter = $characterFactory->tier($tier)->create([
            'user_id' => $adminUser
        ]);
        $adminUser->setRelation('characters', collect([$tierXCharacter]));
        $adminUser->save();

        return Team::factory()->create([
            'tier' => $tier,
            'led_by' => $adminUser->id,
            'created_by' => $adminUser->id,
        ]);
    }
}

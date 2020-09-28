<?php

namespace App\Tests\Integration\JsonApi\Traits;

use App\Models\Team;
use App\Models\User;

trait NeedsTeamStubs
{
    private function stubCustomTeam(User $ledBy, int $tier = 2): Team
    {
        return Team::factory()->create([
            'tier' => $tier,
            'led_by' => $ledBy->id,
            'created_by' => $ledBy->id,
        ]);
    }
}

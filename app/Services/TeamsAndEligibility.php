<?php namespace App\Services;

use App\Models\Team;
use App\Models\User;

class TeamsAndEligibility
{
    public function isUserEligibleToJoin(Team $team, User $user): bool
    {
        foreach ($user->characters as $character) {
            if ($character->approved_for_tier >= $team->tier) {
                return true;
            }
        }

        return false;
    }
}

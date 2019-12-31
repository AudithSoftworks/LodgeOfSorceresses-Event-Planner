<?php namespace App\Services;

use App\Models\Character;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

class TeamsAndEligibility
{
    public function isUserEligibleToJoin(Team $team, User $user): bool
    {
        foreach ($user->characters as $character) {
            $characterEligibility = $character->approved_for_tier >= $team->tier;
            if ($characterEligibility === true) {
                return true;
            }
        }

        return false;
    }

    public function getListOfEligibleCharacters(Team $team, Collection $characters): Collection
    {
        return $characters->filter(static function (Character $character) use ($team) {
            return $character->approved_for_tier >= $team->tier;
        });
    }
}

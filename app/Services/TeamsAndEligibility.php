<?php namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Singleton\RoleTypes;
use Illuminate\Support\Collection;

class TeamsAndEligibility
{
    public function determineTeamMembershipEligibility(Team $team, User $user, int $role = null): bool
    {
        foreach ($user->characters as $character) {
            // $character->role is string when cached, so we convert it back to integer.
            $characterRole = RoleTypes::getRoleId($character->role);
            if ($character->approved_for_tier >= $team->tier) {
                return $role !== null ? $characterRole === $role : true;
            }
        }

        return false;
    }

    public function getListOfEligibleCharactersForGivenTier(User $user, int $tier): Collection
    {
        $eligibleCharactersList = collect();
        foreach ($user->characters as $character) {
            if ($character->approved_for_tier >= $tier) {
                $eligibleCharactersList->add($character->id);
            }
        }

        return $eligibleCharactersList;
    }
}

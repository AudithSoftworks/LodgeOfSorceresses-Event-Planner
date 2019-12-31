<?php namespace App\Services;

use App\Singleton\RoleTypes;
use Illuminate\Support\Collection;

class TeamsAndEligibility
{
    public function determineTeamMembershipEligibility(int $teamId, int $userId, int $role): bool
    {
        app('cache.store')->has('team-' . $teamId); // Recache trigger
        app('cache.store')->has('user-' . $userId); // Recache trigger
        /** @var \App\Models\Team $team */
        $team = app('cache.store')->get('team-' . $teamId);
        /** @var \App\Models\User $user */
        $user = app('cache.store')->get('user-' . $userId);
        foreach ($user->characters as $character) {
            // $character->role is string in Cache, so we convert it back to integer.
            $characterRole = RoleTypes::getRoleId($character->role);
            if ($characterRole === $role && $character->approved_for_tier >= $team->tier) {
                return true;
            }
        }

        return false;
    }

    public function getListOfEligibleCharactersForGivenTier(int $userId, int $tier): Collection
    {
        app('cache.store')->has('user-' . $userId); // Recache trigger
        /** @var \App\Models\User $user */
        $user = app('cache.store')->get('user-' . $userId);
        $eligibleCharactersList = collect();
        foreach ($user->characters as $character) {
            if ($character->approved_for_tier >= $tier) {
                $eligibleCharactersList->add($character);
            }
        }

        return $eligibleCharactersList;
    }
}

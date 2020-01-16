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

    public function isUserMemberOfTeam(Team $team, User $user): bool
    {
        foreach ($team->members as $character) {
            if ($character->teamMembership->status && $character->owner->id === $user->id) {
                return true;
            }
        }

        return false;
    }

    public function isUserMemberOfAnyEndgameTeam(User $user): bool
    {
        foreach ($user->characters as $character) {
            $character->loadMissing('teams');
            foreach ($character->teams as $team) {
                if ($team->teamMembership && $team->teamMembership->status) {
                    return true;
                }
            }
        }

        return false;
    }

    public function areAllMembersOfTeamEligibleForPossibleNewTeamTier(Team $team, int $newTier): bool
    {
        if ($team->tier > $newTier) {
            return true;
        }

        foreach ($team->members as $member) {
            if ($member->approved_for_tier < $newTier) {
                return false;
            }
        }

        return true;
    }
}

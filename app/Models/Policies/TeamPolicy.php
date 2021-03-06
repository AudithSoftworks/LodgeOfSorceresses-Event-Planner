<?php

namespace App\Models\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    use HandlesAuthorization;

    public function canJoin(User $user, Team $team): Response
    {
        return app('teams.eligibility')->isUserEligibleToJoin($team, $user)
            ? $this->allow()
            : $this->deny('User doesn\'t have an eligible character to join this team.');
    }
}

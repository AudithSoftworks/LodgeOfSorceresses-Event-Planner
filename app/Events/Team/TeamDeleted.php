<?php namespace App\Events\Team;

use App\Models\Team;

class TeamDeleted implements TeamNeedsRecacheInterface
{
    public Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
        $team->refresh();
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}

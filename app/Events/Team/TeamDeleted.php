<?php namespace App\Events\Team;

use App\Models\Team;

class TeamDeleted implements TeamNeedsRecacheInterface
{
    /**
     * @var \App\Models\Team
     */
    public $team;

    /**
     * @param \App\Models\Team $team
     */
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

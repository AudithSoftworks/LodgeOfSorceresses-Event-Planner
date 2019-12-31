<?php namespace App\Events\Team;

use App\Models\Team;

class TeamUpdated implements TeamNeedsRecacheInterface
{
    /**
     * @var \App\Models\Team
     */
    public $team;

    /**
     * @var \App\Models\User
     */
    public $owner;

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

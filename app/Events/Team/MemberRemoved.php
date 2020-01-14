<?php

namespace App\Events\Team;

use App\Events\Character\GetCharacterInterface;
use App\Models\Character;
use App\Models\Team;

class MemberRemoved implements GetTeamInterface, GetCharacterInterface
{
    /**
     * @var \App\Models\Character
     */
    public $character;

    /**
     * @var \App\Models\Team
     */
    public $team;

    /**
     * @param \App\Models\Character $character
     * @param \App\Models\Team      $team
     */
    public function __construct(Character $character, Team $team)
    {
        $character->refresh();
        $team->refresh();
        $this->character = $character;
        $this->team = $team;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}

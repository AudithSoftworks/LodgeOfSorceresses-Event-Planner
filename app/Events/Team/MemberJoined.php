<?php

namespace App\Events\Team;

use App\Events\Character\CharacterNeedsRecacheInterface;
use App\Events\User\UserNeedsRecacheInterface;
use App\Models\Character;
use App\Models\Team;
use App\Models\User;

class MemberJoined implements GetTeamInterface, CharacterNeedsRecacheInterface, UserNeedsRecacheInterface
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

    public function getOwner(): User
    {
        return $this->character->owner;
    }
}

<?php

namespace App\Events\Team;

use App\Models\Team;

interface GetTeamInterface
{
    public function getTeam(): Team;
}

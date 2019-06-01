<?php

namespace App\Events\Character;

use App\Models\Character;
use App\Models\User;

interface CharacterInterface
{
    public function getCharacter(): Character;

    public function getOwner(): User;
}

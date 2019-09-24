<?php

namespace App\Events\Character;

use App\Models\Character;

interface GetCharacterInterface
{
    public function getCharacter(): Character;
}

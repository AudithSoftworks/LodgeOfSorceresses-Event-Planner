<?php namespace App\Events\Character;

use App\Events\User\UserNeedsRecacheInterface;
use App\Models\Character;
use App\Models\User;

class CharacterSaved implements UserNeedsRecacheInterface, CharacterNeedsRecacheInterface
{
    public Character $character;

    public function __construct(Character $character)
    {
        $character->refresh();
        $character->loadMissing(['owner']);
        $this->character = $character;
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function getOwner(): ?User
    {
        return $this->character->owner;
    }
}

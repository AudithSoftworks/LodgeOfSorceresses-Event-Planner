<?php namespace App\Events\Character;

use App\Events\User\UserNeedsRecacheInterface;
use App\Models\Character;
use App\Models\User;

class CharacterPromoted implements UserNeedsRecacheInterface, CharacterNeedsRecacheInterface
{
    /**
     * @var \App\Models\Character
     */
    public $character;

    /**
     * @param \App\Models\Character $character
     */
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

    public function getOwner(): User
    {
        return $this->character->owner;
    }
}

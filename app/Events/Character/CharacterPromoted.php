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
     * @var \App\Models\User
     */
    public $owner;

    /**
     * @param \App\Models\Character $character
     */
    public function __construct(Character $character)
    {
        $this->character = $character;
        $character->refresh();
        $character->loadMissing(['owner']);
        $this->owner = $character->owner()->first();
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }
}

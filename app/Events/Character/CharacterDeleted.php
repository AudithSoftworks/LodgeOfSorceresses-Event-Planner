<?php namespace App\Events\Character;

use App\Models\Character;

class CharacterDeleted
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
}

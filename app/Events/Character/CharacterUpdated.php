<?php namespace App\Events\Character;

use App\Models\Character;

class CharacterUpdated implements CharacterNeedsRecacheInterface
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
        $this->character = $character;
        $character->refresh();
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}

<?php namespace App\Events\Character;

use App\Events\DpsParse\GetDpsParsesInterface;
use App\Events\User\UserNeedsRecacheInterface;
use App\Models\Character;
use App\Models\User;

class CharacterDeleting implements UserNeedsRecacheInterface, GetCharacterInterface, GetDpsParsesInterface
{
    /**
     * @var iterable|\App\Models\DpsParse[]
     */
    public $dpsParses;

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
        $character->loadMissing(['owner', 'dpsParses']);
        $this->owner = $character->owner()->first();
        $this->dpsParses = $character->dpsParses()->withTrashed()->get();
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * {@inheritDoc}
     */
    public function getDpsParses()
    {
        return $this->dpsParses;
    }
}

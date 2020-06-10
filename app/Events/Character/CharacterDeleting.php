<?php

namespace App\Events\Character;

use App\Events\DpsParse\GetDpsParsesInterface;
use App\Models\Character;

class CharacterDeleting implements GetDpsParsesInterface
{
    /**
     * @var iterable|\App\Models\DpsParse[]
     */
    public $dpsParses;

    public function __construct(Character $character)
    {
        $character->refresh();
        $character->loadMissing(['dpsParses']);
        $this->dpsParses = $character->dpsParses()->withTrashed()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getDpsParses()
    {
        return $this->dpsParses;
    }
}

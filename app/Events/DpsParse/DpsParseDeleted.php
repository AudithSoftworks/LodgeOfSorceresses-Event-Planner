<?php

namespace App\Events\DpsParse;

use App\Events\Character\CharacterNeedsRecacheInterface;
use App\Models\Character;
use App\Models\DpsParse;

class DpsParseDeleted implements GetDpsParseInterface, CharacterNeedsRecacheInterface
{
    /**
     * @var DpsParse
     */
    public $dpsParse;

    /**
     * @var \App\Models\Character
     */
    public $character;

    public function __construct(DpsParse $dpsParse)
    {
        $this->dpsParse = $dpsParse;
        $this->dpsParse->loadMissing(['character']);
        $this->character = $this->dpsParse->character()->first();
    }

    public function getDpsParse(): DpsParse
    {
        return $this->dpsParse;
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}

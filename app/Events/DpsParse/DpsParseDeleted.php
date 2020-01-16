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

    public function __construct(DpsParse $dpsParse)
    {
        $dpsParse->refresh();
        $dpsParse->loadMissing(['character']);
        $this->dpsParse = $dpsParse;
    }

    public function getDpsParse(): DpsParse
    {
        return $this->dpsParse;
    }

    public function getCharacter(): Character
    {
        return $this->dpsParse->character;
    }
}

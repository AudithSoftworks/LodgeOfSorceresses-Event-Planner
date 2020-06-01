<?php namespace App\Events\DpsParse;

use App\Events\Character\CharacterNeedsRecacheInterface;
use App\Models\Character;
use App\Models\DpsParse;
use App\Models\User;
use App\Events\User\GetUserInterface;

class DpsParseApproved implements GetDpsParseInterface, CharacterNeedsRecacheInterface, GetUserInterface
{
    /**
     * @var \App\Models\DpsParse
     */
    public $dpsParse;

    public function __construct(DpsParse $dpsParse)
    {
        $dpsParse->refresh();
        $dpsParse->loadMissing(['character', 'owner']);
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

    public function getOwner(): ?User
    {
        return $this->dpsParse->character->owner;
    }
}

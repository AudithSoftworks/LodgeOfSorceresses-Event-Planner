<?php namespace App\Events\DpsParse;

use App\Events\Character\CharacterNeedsRecacheInterface;
use App\Models\Character;
use App\Models\DpsParse;
use App\Models\User;
use App\Events\User\GetUserInterface;

class DpsParseDisapproved implements GetDpsParseInterface, CharacterNeedsRecacheInterface, GetUserInterface
{
    /**
     * @var \App\Models\DpsParse
     */
    public $dpsParse;

    /**
     * @var \App\Models\Character
     */
    public $character;

    /**
     * @var \App\Models\User
     */
    public $owner;

    public function __construct(DpsParse $dpsParse)
    {
        $this->dpsParse = $dpsParse;
        $this->dpsParse->loadMissing(['character', 'owner']);
        $this->character = $this->dpsParse->character()->first();
        $this->owner = $this->dpsParse->owner()->first();
    }

    public function getDpsParse(): DpsParse
    {
        return $this->dpsParse;
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

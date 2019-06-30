<?php

namespace App\Extensions\Socialite;

use Laravel\Socialite\Two\User;

class CustomOauthTwoUser extends User
{
    /**
     * @var int
     */
    public $remotePrimaryGroup;

    /**
     * @var array
     */
    public $remoteSecondaryGroups;

    /**
     * @var bool
     */
    public $verified;

    /**
     * @return int|null
     */
    public function getRemotePrimaryGroup(): ?int
    {
        return $this->remotePrimaryGroup;
    }

    /**
     * @return array|null
     */
    public function getRemoteSecondaryGroups(): ?array
    {
        return $this->remoteSecondaryGroups;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }
}

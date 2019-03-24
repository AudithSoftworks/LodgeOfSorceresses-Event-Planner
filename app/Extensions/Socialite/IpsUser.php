<?php

namespace App\Extensions\Socialite;

use Laravel\Socialite\Two\User;

class IpsUser extends User
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
     * @return int
     */
    public function getRemotePrimaryGroup(): int
    {
        return $this->remotePrimaryGroup;
    }

    /**
     * @param int $remotePrimaryGroup
     *
     * @return $this
     */
    public function setRemotePrimaryGroup(int $remotePrimaryGroup): self
    {
        $this->remotePrimaryGroup = $remotePrimaryGroup;

        return $this;
    }

    /**
     * @return array
     */
    public function getRemoteSecondaryGroups(): array
    {
        return $this->remoteSecondaryGroups;
    }

    /**
     * @param array $remoteSecondaryGroups
     *
     * @return $this
     */
    public function setRemoteSecondaryGroups(array $remoteSecondaryGroups): self
    {
        $this->remoteSecondaryGroups = $remoteSecondaryGroups;

        return $this;
    }
}

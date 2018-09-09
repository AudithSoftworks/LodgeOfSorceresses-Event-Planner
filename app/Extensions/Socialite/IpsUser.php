<?php

namespace App\Extensions\Socialite;

use Laravel\Socialite\Two\User;

class IpsUser extends User
{
    /**
     * @var int
     */
    public $remoteMemberGroup;

    /**
     * @return int
     */
    public function getRemoteMemberGroup(): int
    {
        return $this->remoteMemberGroup;
    }

    /**
     * @param int $remoteMemberGroup
     *
     * @return IpsUser
     */
    public function setRemoteMemberGroup(int $remoteMemberGroup): IpsUser
    {
        $this->remoteMemberGroup = $remoteMemberGroup;

        return $this;
    }
}

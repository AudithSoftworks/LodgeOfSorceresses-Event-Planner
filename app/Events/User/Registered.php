<?php

namespace App\Events\User;

use App\Models\User;

class Registered
{
    public User $user;

    public ?string $oauthProvider;

    public function __construct(User $user, ?string $oauthProviderName = null)
    {
        $this->user = $user;
        $this->oauthProvider = $oauthProviderName;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOauthProvider(): ?string
    {
        return $this->oauthProvider;
    }
}

<?php namespace App\Events\User;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class OnboardingCompleted implements GetUserInterface
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User
     */
    public Authenticatable $user;

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    public function getOwner(): ?User
    {
        return $this->user;
    }
}

<?php namespace App\Events\User;

use App\Models\User;

class NameUpdated implements UserNeedsRecacheInterface
{
    /**
     * @var \App\Models\User
     */
    public $user;

    /**
     * @param \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user
     */
    public function __construct(User $user)
    {
        $user->refresh();
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->getOwner();
    }

    public function getOwner(): ?User
    {
        return $this->user;
    }
}

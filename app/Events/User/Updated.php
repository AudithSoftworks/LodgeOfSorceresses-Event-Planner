<?php namespace App\Events\User;

use App\Models\User;

class Updated
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
        $this->user = $user;
    }
}

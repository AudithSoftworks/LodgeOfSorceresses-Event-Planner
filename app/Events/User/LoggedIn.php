<?php namespace App\Events\User;

use App\Models\User;

class LoggedIn
{
    /**
     * @var \App\Models\User
     */
    public $user;

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}

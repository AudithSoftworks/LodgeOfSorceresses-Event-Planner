<?php namespace App\Events\User;

use Illuminate\Contracts\Auth\Authenticatable;

class LoggedOut
{
    /**
     * @var array|\Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}

<?php namespace App\Events\Users;

use Illuminate\Contracts\Auth\Authenticatable;

class RequestedActivationLink
{
    /**
     * @var array|\Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}

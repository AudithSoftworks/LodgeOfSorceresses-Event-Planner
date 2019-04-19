<?php namespace App\Events\User;

use Illuminate\Contracts\Auth\Authenticatable;

class Registered
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * @var null|string
     */
    public $oauthProviderName;

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string|null                                $oauthProviderName
     */
    public function __construct(Authenticatable $user, $oauthProviderName = null)
    {
        $this->user = $user;
        $this->oauthProviderName = $oauthProviderName;
    }
}

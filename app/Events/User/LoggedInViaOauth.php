<?php namespace App\Events\User;

use App\Models\UserOAuth;

class LoggedInViaOauth
{
    /**
     * @var \App\Models\UserOAuth
     */
    public $oauthAccount;

    /**
     * @var bool
     */
    public $forceOauthUpdateViaApi;

    /**
     * @param \App\Models\UserOAuth $oauthAccount
     */
    public function __construct(UserOAuth $oauthAccount)
    {
        $this->oauthAccount = $oauthAccount;
    }
}

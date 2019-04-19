<?php namespace App\Events\User;

use App\Models\UserOAuth;

class LoggedInViaIpsOauth
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
     * @param bool                  $forceOauthUpdateViaApi
     */
    public function __construct(UserOAuth $oauthAccount, $forceOauthUpdateViaApi = false)
    {
        $this->oauthAccount = $oauthAccount;
        $this->forceOauthUpdateViaApi = $forceOauthUpdateViaApi;
    }
}

<?php

namespace App\Policies;

use App\Services\IpsApi;
use Illuminate\Auth\Access\HandlesAuthorization;

class DpsParsePolicy
{
    use HandlesAuthorization;

    /**
     * @var \App\Models\UserOAuth
     */
    private $oauthAccount;

    public function __construct()
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->load('linkedAccounts');
        $this->oauthAccount = $me->linkedAccounts()->where('remote_provider', 'ips')->first();
        $this->oauthAccount->remote_secondary_groups = explode(',', $this->oauthAccount->remote_secondary_groups);
    }

    /**
     * @return bool
     */
    public function admin(): bool
    {
        return $this->oauthAccount
            && (
                $this->oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_IPSISSIMUS
                || $this->oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI
                || in_array(IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI, $this->oauthAccount->remote_secondary_groups, false)
            );
    }

    /**
     * @return bool
     */
    public function view(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function create(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function update(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }
}

<?php

namespace App\Policies;

use App\Services\DiscordApi;
use App\Services\IpsApi;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * @var \App\Models\UserOAuth
     */
    private $oauthAccount;

    public function admin(): bool
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->loadMissing('linkedAccounts');
        $oauthAccount = $me->linkedAccounts()->where('remote_provider', 'ips')->first();
        if ($oauthAccount && !empty($oauthAccount->remote_secondary_groups)) {
            $oauthAccount->remote_secondary_groups = explode(',', $oauthAccount->remote_secondary_groups);
        }

        return $oauthAccount
            && $oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN
            && (
                $oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_IPSISSIMUS
                || $oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI
                || in_array(IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI, $oauthAccount->remote_secondary_groups, false)
            );
    }

    public function user(): bool
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->loadMissing('linkedAccounts');
        $oauthAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        if ($oauthAccount) {
            if (!empty($oauthAccount->remote_secondary_groups)) {
                $oauthAccount->remote_secondary_groups = explode(',', $oauthAccount->remote_secondary_groups);
            }

            return !empty($oauthAccount->remote_secondary_groups)
                && (
                    in_array(DiscordApi::ROLE_SOULSHRIVEN, $oauthAccount->remote_secondary_groups, true)
                    || in_array(DiscordApi::ROLE_MEMBERS, $oauthAccount->remote_secondary_groups, true)
                );
        }

        return false;
    }

    public function limited(): bool
    {
        return app('auth.driver')->check();
    }
}

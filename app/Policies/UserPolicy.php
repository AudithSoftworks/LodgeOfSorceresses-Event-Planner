<?php

namespace App\Policies;

use App\Services\DiscordApi;
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
        $oauthAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        if ($oauthAccount) {
            $oauthAccount->remote_secondary_groups = !empty($oauthAccount->remote_secondary_groups)
                ? explode(',', $oauthAccount->remote_secondary_groups)
                : [];

            return in_array(DiscordApi::ROLE_MAGISTER_TEMPLI, $oauthAccount->remote_secondary_groups, true);
        }

        return false;
    }

    public function user(): bool
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $oauthAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        if ($oauthAccount) {
            $oauthAccount->remote_secondary_groups = !empty($oauthAccount->remote_secondary_groups)
                ? explode(',', $oauthAccount->remote_secondary_groups)
                : [];

            return in_array(DiscordApi::ROLE_SOULSHRIVEN, $oauthAccount->remote_secondary_groups, true)
                || in_array(DiscordApi::ROLE_MEMBERS, $oauthAccount->remote_secondary_groups, true);
        }

        return false;
    }

    public function limited(): bool
    {
        return app('auth.driver')->check();
    }
}

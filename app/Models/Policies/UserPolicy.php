<?php

namespace App\Models\Policies;

use App\Services\DiscordApi;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    use HandlesAuthorization;

    public function admin(?Authenticatable $user = null): bool
    {
        return in_array(DiscordApi::ROLE_MAGISTER_TEMPLI, $this->getDiscordSecondaryGroupsForGivenUser($user), true);
    }

    public function user(?Authenticatable $user = null): bool
    {
        $discordSecondaryGroupsForCurrentUser = $this->getDiscordSecondaryGroupsForGivenUser($user);

        return in_array(DiscordApi::ROLE_SOULSHRIVEN, $discordSecondaryGroupsForCurrentUser, true)
            || in_array(DiscordApi::ROLE_MEMBERS, $discordSecondaryGroupsForCurrentUser, true);
    }

    public function soulshriven(?Authenticatable $user = null): bool
    {
        return in_array(DiscordApi::ROLE_SOULSHRIVEN, $this->getDiscordSecondaryGroupsForGivenUser($user), true);
    }

    public function member(?Authenticatable $user = null): bool
    {
        return in_array(DiscordApi::ROLE_MEMBERS, $this->getDiscordSecondaryGroupsForGivenUser($user), true);
    }

    public function limited(): bool
    {
        return Auth::check();
    }

    /**
     * @param null|\Illuminate\Contracts\Auth\Authenticatable|\App\Models\User $user
     *
     * @return string[]
     */
    private function getDiscordSecondaryGroupsForGivenUser(?Authenticatable $user): array
    {
        $user = $user ?? Auth::user();
        if ($user !== null) {
            /** @var \App\Models\UserOAuth $usersDiscordAccount */
            $usersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
            if ($usersDiscordAccount !== null) {
                return !empty($usersDiscordAccount->remote_secondary_groups)
                    ? explode(',', $usersDiscordAccount->remote_secondary_groups)
                    : [];
            }
        }

        return [];
    }
}

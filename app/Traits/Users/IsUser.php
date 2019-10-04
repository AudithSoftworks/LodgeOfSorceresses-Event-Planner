<?php

namespace App\Traits\Users;

use App\Models\User;
use App\Services\DiscordApi;

trait IsUser
{
    public function parseLinkedAccounts(User $user): void
    {
        $linkedAccountsParsed = $user->linkedAccounts->keyBy('remote_provider');
        $user->isMember = $user->isSoulshriven = false;
        foreach ($linkedAccountsParsed as $linkedAccount) {
            if (!empty($linkedAccount->remote_secondary_groups)) {
                $linkedAccount->remote_secondary_groups = explode(',', $linkedAccount->remote_secondary_groups);
                if ($linkedAccount->remote_provider === 'discord') {
                    $user->isMember = in_array(DiscordApi::ROLE_MEMBERS, $linkedAccount->remote_secondary_groups, true);
                    $user->isSoulshriven = in_array(DiscordApi::ROLE_SOULSHRIVEN, $linkedAccount->remote_secondary_groups, true);
                }
            }
        }
        $user->linkedAccountsParsed = $linkedAccountsParsed;
        $user->makeVisible(['linkedAccountsParsed', 'isMember', 'isSoulshriven']);
        $user->makeHidden(['linkedAccounts']);
    }
}

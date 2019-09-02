<?php

namespace App\Listeners\User;

use App\Events\User\Updated;

class UpdateDiscordAndForumNames
{
    /**
     * @param \App\Events\User\Updated $event
     *
     * @return bool
     */
    public function handle(Updated $event): bool
    {
        $user = $event->user;
        $user->refresh();
        $ingameUserId = $user->name;
        /** @var \App\Models\UserOAuth $linkedAccount */
        foreach ($linkedAccounts = $user->linkedAccounts()->get() as $linkedAccount) {
            if ($linkedAccount->remote_provider === 'ips') {
                app('ips.api')->editUser($linkedAccount->remote_id, ['name' => $ingameUserId]);
            } elseif ($linkedAccount->remote_provider === 'discord') {
                app('discord.api')->modifyGuildMember($linkedAccount->remote_id, ['nick' => $ingameUserId]);
            }
        }

        return true;
    }
}

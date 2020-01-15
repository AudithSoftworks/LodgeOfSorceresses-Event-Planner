<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberJoined;
use Illuminate\Support\Facades\Cache;

class AssignDiscordTagToMemberJoining
{
    /**
     * @param \App\Events\Team\MemberJoined $event
     *
     * @return bool
     */
    public function handle(MemberJoined $event): bool
    {
        $team = $event->getTeam();
        $character = $event->getCharacter();

        $discordRoleId = (string)$team->discord_id;
        $user = $character->owner;
        /** @var \App\Models\UserOAuth $usersDiscordAccount */
        $usersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        $usersDiscordRoles = collect(explode(',', $usersDiscordAccount->remote_secondary_groups));
        if (!$usersDiscordRoles->contains($discordRoleId)) {
            $usersDiscordRoles->add($discordRoleId);

            app('discord.api')->modifyGuildMember($usersDiscordAccount->remote_id, ['roles' => $usersDiscordRoles->values()]);

            $usersDiscordAccount->remote_secondary_groups = $usersDiscordRoles->implode(',');
            $usersDiscordAccount->save();
            Cache::forget('user-' . $user->id);
        }

        return true;
    }
}

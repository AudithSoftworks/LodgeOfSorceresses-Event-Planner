<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberRemoved;
use Illuminate\Support\Facades\Cache;

class RemoveDiscordTagFromMemberLeaving
{
    /**
     * @param \App\Events\Team\MemberRemoved $event;
     *
     * @return bool
     */
    public function handle(MemberRemoved $event): bool
    {
        $team = $event->getTeam();
        $character = $event->getCharacter();

        $discordRoleId = (string)$team->discord_id;
        $user = $character->owner;
        /** @var \App\Models\UserOAuth $usersDiscordAccount */
        $usersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        $usersDiscordRoles = collect(explode(',', $usersDiscordAccount->remote_secondary_groups));
        if (!app('teams.eligibility')->isUserMemberOfTeam($team, $user)) {
            $usersDiscordRoles = $usersDiscordRoles->reject(static function ($item) use ($discordRoleId) {
                return $item === $discordRoleId;
            });

            app('discord.api')->modifyGuildMember($usersDiscordAccount->remote_id, ['roles' => $usersDiscordRoles->values()]);

            $usersDiscordAccount->remote_secondary_groups = $usersDiscordRoles->implode(',');
            $usersDiscordAccount->save();
            Cache::forget('user-' . $user->id);
        }

        return true;
    }
}

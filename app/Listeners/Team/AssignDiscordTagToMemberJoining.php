<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberJoined;
use App\Services\DiscordApi;
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
        $dominusLiminisRoleId = DiscordApi::ROLE_DOMINUS_LIMINIS;
        $user = $character->owner;
        /** @var \App\Models\UserOAuth $usersDiscordAccount */
        $usersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        $usersDiscordRoles = collect(explode(',', $usersDiscordAccount->remote_secondary_groups))
            ->add($discordRoleId)
            ->add($dominusLiminisRoleId)
            ->unique();

        app('discord.api')->modifyGuildMember($usersDiscordAccount->remote_id, ['roles' => $usersDiscordRoles->values()]);

        $usersDiscordAccount->remote_secondary_groups = $usersDiscordRoles->implode(',');
        $usersDiscordAccount->save();

        return true;
    }
}

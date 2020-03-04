<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberJoined;
use App\Services\DiscordApi;
use App\Services\TeamsAndEligibility;

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
        if ($team->tier > TeamsAndEligibility::TRAINING_TEAM_TIER) {
            $character = $event->getCharacter();

            $discordRoleId = $team->discord_id;
            $user = $character->owner;
            /** @var \App\Models\UserOAuth $usersDiscordAccount */
            $usersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
            $usersDiscordRoles = collect(explode(',', $usersDiscordAccount->remote_secondary_groups))
                ->add($discordRoleId)
                ->add(DiscordApi::ROLE_DOMINUS_LIMINIS)
                ->unique();

            app('discord.api')->modifyGuildMember($usersDiscordAccount->remote_id, ['roles' => $usersDiscordRoles->values()]);

            $usersDiscordAccount->remote_secondary_groups = $usersDiscordRoles->implode(',');
            $usersDiscordAccount->isDirty() && $usersDiscordAccount->save();
        }

        return true;
    }
}

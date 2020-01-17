<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberRemoved;
use App\Services\DiscordApi;
use App\Services\GuildRanksAndClearance;
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

        $discordRoleId = $team->discord_id;
        $user = $character->owner;
        /** @var \App\Models\UserOAuth $usersDiscordAccount */
        $usersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        $usersDiscordRoles = collect(explode(',', $usersDiscordAccount->remote_secondary_groups));
        $teamsAndEligibilityService = app('teams.eligibility');
        if (!$teamsAndEligibilityService->isUserMemberOfTeam($team, $user)) {
            $usersDiscordRoles = $usersDiscordRoles->reject(static function ($item) use ($discordRoleId) {
                return $item === $discordRoleId;
            });
            $usersDiscordAccount->remote_secondary_groups = $usersDiscordRoles->implode(',');
        }

        $dominusLiminisRoleId = DiscordApi::ROLE_DOMINUS_LIMINIS;
        if (!$teamsAndEligibilityService->isUserMemberOfAnyEndgameTeam($user)) {
            $usersDiscordRoles = $usersDiscordRoles->reject(static function ($item) use ($dominusLiminisRoleId) {
                return $item === $dominusLiminisRoleId;
            });
            $usersDiscordAccount->remote_secondary_groups = $usersDiscordRoles->implode(',');
        }

        if ($usersDiscordAccount->isDirty()) {
            app('discord.api')->modifyGuildMember($usersDiscordAccount->remote_id, ['roles' => $usersDiscordRoles->values()]);
            $usersDiscordAccount->save();
        }

        return true;
    }
}
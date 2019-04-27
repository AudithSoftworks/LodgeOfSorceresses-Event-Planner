<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseApproved;
use App\Models\User;
use App\Services\DiscordApi;
use App\Services\GuildRankAndClearance;
use App\Services\IpsApi;

class RerankPlayerOnIpsAndDiscordUponDpsParseApproval
{
    /**
     * @param \App\Events\DpsParse\DpsParseApproved $event
     *
     * @return bool|int
     */
    public function handle(DpsParseApproved $event)
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();

        $dpsParse->load(['owner']);
        /** @var \App\Models\User $parseAuthor */
        $parseAuthor = $dpsParse->owner()->first();
        $parseAuthor->loadMissing(['linkedAccounts', 'characters']);

        $topClearanceExisting = app('guild.ranks.clearance')->calculateTopClearanceForUser($parseAuthor);
        $this->rerankUserOnIps($parseAuthor, $topClearanceExisting);
        $this->rerankUserOnDiscord($parseAuthor, $topClearanceExisting);

        return true;
    }

    private function rerankUserOnIps(User $user, ?string $clearanceLevel): void
    {
        $user->loadMissing(['linkedAccounts']);
        /** @var \App\Models\UserOAuth $parseAuthorIpsAccount */
        $remoteIpsUser = $user->linkedAccounts()->where('remote_provider', 'ips')->first();
        if (!$remoteIpsUser) {
            return;
        }

        if (in_array($remoteIpsUser->remote_primary_group, [IpsApi::MEMBER_GROUPS_IPSISSIMUS, IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI], false)) {
            return;
        }

        $memberGroupId = $clearanceLevel ? GuildRankAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['ipsGroupId'] : IpsApi::MEMBER_GROUPS_INITIATE;

        app('ips.api')->editUser($remoteIpsUser->remote_id, ['group' => $memberGroupId]);

        $remoteIpsUser->remote_primary_group = $memberGroupId;
        $remoteIpsUser->save();
    }

    private function rerankUserOnDiscord(User $user, ?string $clearanceLevel): void
    {
        $user->loadMissing(['linkedAccounts']);
        /** @var \App\Models\UserOAuth $remoteDiscordUser */
        $remoteDiscordUser = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        if (!$remoteDiscordUser) {
            return;
        }

        $existingSpecialRoles = array_intersect(explode(',', $remoteDiscordUser->remote_secondary_groups), [
            DiscordApi::ROLE_DAMAGE_DEALER,
            DiscordApi::ROLE_TANK,
            DiscordApi::ROLE_HEALER,
            DiscordApi::ROLE_MAGISTER_TEMPLI,
            DiscordApi::ROLE_RAID_LEADERS,
            DiscordApi::ROLE_GUIDANCE,
            DiscordApi::ROLE_DOMINUS_LIMINIS,
            DiscordApi::ROLE_MEMBERS,
            DiscordApi::ROLE_RECTOR,
            DiscordApi::ROLE_CORE_ONE,
            DiscordApi::ROLE_CORE_TWO,
            DiscordApi::ROLE_CORE_THREE,
        ]);
        $newRoleToAssign = $clearanceLevel ? GuildRankAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['discordRole'] : DiscordApi::ROLE_INITIATE;
        $rolesToAssign = array_merge($existingSpecialRoles, [$newRoleToAssign]);
        $result = app('discord.api')->modifyGuildMember($remoteDiscordUser->remote_id, ['roles' => $rolesToAssign]);
        if ($result) {
            $remoteDiscordUser->remote_secondary_groups = implode(',', $rolesToAssign);
            $remoteDiscordUser->save();
        }
    }
}

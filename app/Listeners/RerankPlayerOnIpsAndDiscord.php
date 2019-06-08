<?php namespace App\Listeners;

use App\Events\Character\CharacterInterface;
use App\Events\Character\DpsParseInterface;
use App\Models\UserOAuth;
use App\Services\DiscordApi;
use App\Services\GuildRankAndClearance;
use App\Services\IpsApi;
use GuzzleHttp\RequestOptions;

class RerankPlayerOnIpsAndDiscord
{
    public function __construct()
    {
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * @param \App\Events\Character\CharacterInterface|\App\Events\Character\DpsParseInterface $event
     *
     * @return bool|int
     */
    public function handle($event)
    {
        $parseAuthor = null;
        if ($event instanceof CharacterInterface) {
            $character = $event->getCharacter();
            $character->refresh();
            $character->loadMissing(['owner']);
            $parseAuthor = $event->getOwner();
            $parseAuthor->refresh();
        } elseif ($event instanceof DpsParseInterface) {
            $dpsParse = $event->getDpsParse();
            $dpsParse->refresh();
            $dpsParse->load(['owner']);
            /** @var \App\Models\User $parseAuthor */
            $parseAuthor = $dpsParse->owner()->first();
        }
        if (!$parseAuthor) {
            return false;
        }

        $parseAuthor->loadMissing(['linkedAccounts', 'characters']);

        $discordApi = app('discord.api');
        $topClearanceExisting = app('guild.ranks.clearance')->calculateTopClearanceForUser($parseAuthor);

        /** @var \App\Models\UserOAuth $parseOwnersIpsAccount */
        $parseOwnersIpsAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'ips')->first();
        /** @var \App\Models\UserOAuth $parseOwnersDiscordAccount */
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        $mentionedName = $parseOwnersDiscordAccount ? '<@!' . $parseOwnersDiscordAccount->remote_id . '>' : $parseAuthor->name;
        $parseOwnersIpsAccount && $this->rerankUserOnIps($parseOwnersIpsAccount, $topClearanceExisting);
        $parseOwnersDiscordAccount && $this->rerankUserOnDiscord($parseOwnersDiscordAccount, $topClearanceExisting);
        $parseOwnersDiscordAccount && $this->announceRerankToThePlayerViaDiscordDm($discordApi, $parseOwnersDiscordAccount, $mentionedName, $topClearanceExisting);
        $this->announceRerankInOfficerChannelOnDiscord($discordApi, $mentionedName, $topClearanceExisting);

        return true;
    }

    private function rerankUserOnIps(UserOAuth $remoteIpsUser, ?string $clearanceLevel): void
    {
        if (in_array($remoteIpsUser->remote_primary_group, [IpsApi::MEMBER_GROUPS_IPSISSIMUS, IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI], false)) {
            return;
        }

        $memberGroupId = $clearanceLevel ? GuildRankAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['ipsGroupId'] : IpsApi::MEMBER_GROUPS_INITIATE;

        app('ips.api')->editUser($remoteIpsUser->remote_id, ['group' => $memberGroupId]);

        $remoteIpsUser->remote_primary_group = $memberGroupId;
        $remoteIpsUser->save();
    }

    private function rerankUserOnDiscord(UserOAuth $remoteDiscordUser, ?string $clearanceLevel): void
    {
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

    private function announceRerankToThePlayerViaDiscordDm(DiscordApi $discordApi, UserOAuth $remoteDiscordUser,  string $mentionedName, ?string $playerClearance): void
    {
        $playerNewRankTitle = $playerClearance
            ? GuildRankAndClearance::CLEARANCE_LEVELS[$playerClearance]['rank']['title']
            : GuildRankAndClearance::RANK_INITIATE['title'];

        $dmChannel = $discordApi->createDmChannel($remoteDiscordUser->remote_id);
        $discordApi->createMessageInChannel($dmChannel['id'], [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ', recent activities of your Characters (DPS approval, Tank/Healer clearance, Character deletion etc) on Planner triggered a Reranking!'
                        . ' As a result of this, your current member rank was updated to **' . $playerNewRankTitle . '**',
                    'tts' => false,
                ]),
            ]
        ]);
    }

    private function announceRerankInOfficerChannelOnDiscord(DiscordApi $discordApi, string $mentionedName, ?string $playerClearance): void
    {
        $officerChannelId = config('services.discord.channels.officer_hq');

        $mentionedOfficerGroup = '<@&' . GuildRankAndClearance::RANK_MAGISTER_TEMPLI['discordRole'] . '>';
        $rankTitle = $playerClearance ? GuildRankAndClearance::CLEARANCE_LEVELS[$playerClearance]['rank']['title'] : GuildRankAndClearance::RANK_INITIATE['title'];

        $discordApi->createMessageInChannel($officerChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedOfficerGroup . ': ' . $mentionedName . ' needs to have in-game guild rank of **' . $rankTitle . '**. Please promote/demote them accordingly!',
                    'tts' => false,
                ]),
            ]
        ]);
    }
}
<?php namespace App\Listeners;

use App\Models\UserOAuth;
use App\Services\DiscordApi;
use App\Services\GuildRankAndClearance;
use App\Services\IpsApi;
use App\Singleton\RoleTypes;
use GuzzleHttp\RequestOptions;

class RerankPlayerOnIpsAndDiscord
{
    /**
     * @var \App\Models\Character
     */
    private $character;

    public function __construct()
    {
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * @param \App\Events\Character\GetCharacterInterface|\App\Events\User\GetUserInterface|\App\Events\DpsParse\GetDpsParsesInterface $event
     *
     * @return bool|int
     */
    public function handle($event)
    {
        $this->character = $event->getCharacter();
        $parseAuthor = $event->getOwner();

        $parseAuthor->loadMissing(['linkedAccounts', 'characters']);

        $discordApi = app('discord.api');
        $guildRankAndClearance = app('guild.ranks.clearance');
        $newOverallClearanceForUser = $guildRankAndClearance->calculateCumulativeClearanceOfUser($parseAuthor);
        $userShouldRetainRoleTagOnDiscord = $guildRankAndClearance->determineIfUserHasOtherRankedCharactersWithGivenRole($parseAuthor, $this->character->role);

        /** @var \App\Models\UserOAuth $parseOwnersIpsAccount */
        $parseOwnersIpsAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'ips')->first();
        /** @var \App\Models\UserOAuth $parseOwnersDiscordAccount */
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        $mentionedName = $parseOwnersDiscordAccount ? '<@!' . $parseOwnersDiscordAccount->remote_id . '>' : $parseAuthor->name;
        $parseOwnersIpsAccount && $this->rerankUserOnIps($parseOwnersIpsAccount, $newOverallClearanceForUser);
        $parseOwnersDiscordAccount && $this->rerankUserOnDiscord($parseOwnersDiscordAccount, $newOverallClearanceForUser, $userShouldRetainRoleTagOnDiscord);
        $parseOwnersDiscordAccount && $this->announceRerankToThePlayerViaDiscordDm($discordApi, $parseOwnersDiscordAccount, $mentionedName, $newOverallClearanceForUser);
        $isParseOwnerASoulshriven = in_array(DiscordApi::ROLE_SOULSHRIVEN, explode(',', $parseOwnersDiscordAccount->remote_secondary_groups), true);
        !$isParseOwnerASoulshriven && $this->announceRerankInOfficerChannelOnDiscord($discordApi, $mentionedName, $newOverallClearanceForUser);

        return true;
    }

    private function rerankUserOnIps(UserOAuth $remoteIpsUser, ?string $clearanceLevel): void
    {
        if (in_array($remoteIpsUser->remote_primary_group, [IpsApi::MEMBER_GROUPS_IPSISSIMUS, IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI], false)) {
            return;
        }

        $memberGroupId = $clearanceLevel ? GuildRankAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['ipsGroupId'] : IpsApi::MEMBER_GROUPS_INITIATE;

        if ($remoteIpsUser->remote_primary_group === IpsApi::MEMBER_GROUPS_SOULSHRIVEN) {
            $remoteSecondaryGroups = [(string)$memberGroupId];
            app('ips.api')->editUser($remoteIpsUser->remote_id, ['secondaryGroups' => $remoteSecondaryGroups]);
            $remoteIpsUser->remote_secondary_groups = implode(',', $remoteSecondaryGroups);
        } else {
            app('ips.api')->editUser($remoteIpsUser->remote_id, ['group' => $memberGroupId]);
            $remoteIpsUser->remote_primary_group = $memberGroupId;
        }

        $remoteIpsUser->save();
    }

    private function rerankUserOnDiscord(UserOAuth $remoteDiscordUser, ?string $clearanceLevel, bool $userShouldRetainRoleTagOnDiscord): void
    {
        $usersDiscordRoles = explode(',', $remoteDiscordUser->remote_secondary_groups);
        $existingSpecialRoles = array_intersect($usersDiscordRoles, [
            DiscordApi::ROLE_DAMAGE_DEALER,
            DiscordApi::ROLE_TANK,
            DiscordApi::ROLE_HEALER,
            DiscordApi::ROLE_MAGISTER_TEMPLI,
            DiscordApi::ROLE_RAID_LEADERS,
            DiscordApi::ROLE_GUIDANCE,
            DiscordApi::ROLE_DOMINUS_LIMINIS,
            DiscordApi::ROLE_MEMBERS,
            DiscordApi::ROLE_SOULSHRIVEN,
            DiscordApi::ROLE_ADEPTUS_EXEMPTUS,
            DiscordApi::ROLE_CORE_ONE,
            DiscordApi::ROLE_CORE_TWO,
            DiscordApi::ROLE_CORE_THREE,
        ]);
        $discordRole = null;
        switch ($this->character->role) {
            case RoleTypes::ROLE_TANK:
                $discordRole = DiscordApi::ROLE_TANK;
                break;
            case RoleTypes::ROLE_HEALER:
                $discordRole = DiscordApi::ROLE_HEALER;
                break;
            case RoleTypes::ROLE_MAGICKA_DD:
            case RoleTypes::ROLE_STAMINA_DD:
                $discordRole = DiscordApi::ROLE_DAMAGE_DEALER;
                break;
        }
        if ($discordRole) {
            $keyInDiscordRoleArray = array_search($discordRole, $existingSpecialRoles, true);
            if ($userShouldRetainRoleTagOnDiscord && !$keyInDiscordRoleArray) {
                $existingSpecialRoles[] = $discordRole;
            } elseif (!$userShouldRetainRoleTagOnDiscord && $keyInDiscordRoleArray) {
                unset($existingSpecialRoles[$keyInDiscordRoleArray]);
            }
        }

        $newRoleToAssign = $clearanceLevel ? GuildRankAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['discordRole'] : DiscordApi::ROLE_INITIATE;
        if (in_array(DiscordApi::ROLE_SOULSHRIVEN, $usersDiscordRoles, true)) {
            $newRoleToAssign = $clearanceLevel ? GuildRankAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['discordShrivenRole'] : DiscordApi::ROLE_SOULSHRIVEN;
        }
        $rolesToAssign = array_unique(array_merge($existingSpecialRoles, [$newRoleToAssign]));
        $result = app('discord.api')->modifyGuildMember($remoteDiscordUser->remote_id, ['roles' => $rolesToAssign]);
        if ($result) {
            $remoteDiscordUser->remote_secondary_groups = implode(',', $rolesToAssign);
            $remoteDiscordUser->save();
        }
    }

    private function announceRerankToThePlayerViaDiscordDm(DiscordApi $discordApi, UserOAuth $remoteDiscordUser, string $mentionedName, ?string $playerClearance): void
    {
        $playerNewRankTitle = $playerClearance
            ? GuildRankAndClearance::CLEARANCE_LEVELS[$playerClearance]['rank']['title']
            : GuildRankAndClearance::RANK_INITIATE['title'];

        $dmChannel = $discordApi->createDmChannel($remoteDiscordUser->remote_id);
        $responseDecoded = $discordApi->createMessageInChannel($dmChannel['id'], [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ', recent activities of your Characters (DPS approval, Tank/Healer clearance, Character deletion etc) on Planner triggered a Reranking!'
                        . ' As a result of this, your current member rank was updated to **' . $playerNewRankTitle . '**',
                    'tts' => false,
                ]),
            ]
        ]);
        $discordApi->reactToMessageInChannel($dmChannel['id'], $responseDecoded['id'], '☝');
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

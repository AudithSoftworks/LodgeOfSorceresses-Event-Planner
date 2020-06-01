<?php namespace App\Listeners;

use App\Events\Team\GetTeamInterface;
use App\Models\UserOAuth;
use App\Services\DiscordApi;
use App\Services\GuildRanksAndClearance;
use Cloudinary;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Gate;

class RerankPlayerOnDiscord
{
    public function __construct()
    {
        Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * @param \App\Events\Character\CharacterNeedsRecacheInterface|\App\Events\User\UserNeedsRecacheInterface $event
     *
     * @throws \JsonException
     * @return bool|int
     */
    public function handle($event)
    {
        if (($parseAuthor = $event->getOwner()) === null) {
            return false;
        }
        $parseAuthor->loadMissing(['linkedAccounts', 'characters']);

        $guildRankAndClearanceService = app('guild.ranks.clearance');
        $membershipMode = Gate::forUser($parseAuthor)->allows('is-member') ? DiscordApi::ROLE_MEMBERS : null;
        if ($membershipMode === null) {
            $membershipMode = Gate::forUser($parseAuthor)->allows('is-soulshriven') ? DiscordApi::ROLE_SOULSHRIVEN : null;
        }
        if ($membershipMode === null) {
            return false;
        }
        $clearanceLevel = $guildRankAndClearanceService->refreshGivenUsersDiscordRoles($parseAuthor, $membershipMode);

        $discordApi = app('discord.api');
        /** @var null|\App\Models\UserOAuth $parseOwnersDiscordAccount */
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        if ($parseOwnersDiscordAccount && !($event instanceof GetTeamInterface)) { // Don't ping officers when someone joins/leaves a Team.
            $this->announceRerankInOfficerChannelOnDiscord($discordApi, $parseOwnersDiscordAccount, $clearanceLevel);
        }

        return true;
    }

    /**
     * @param \App\Services\DiscordApi $discordApi
     * @param \App\Models\UserOAuth    $remoteDiscordUser
     * @param int                      $clearanceLevel
     *
     * @throws \JsonException
     */
    private function announceRerankInOfficerChannelOnDiscord(DiscordApi $discordApi, UserOAuth $remoteDiscordUser, int $clearanceLevel): void
    {
        $officerChannelId = config('services.discord.channels.officer_logs');

        $mentionedName = '<@!' . $remoteDiscordUser->remote_id . '>';

        $mentionedOfficerGroup = '<@&' . GuildRanksAndClearance::RANK_MAGISTER_TEMPLI['discordRole'] . '>';
        $rankTitle = $clearanceLevel ? GuildRanksAndClearance::CLEARANCE_LEVELS[$clearanceLevel]['rank']['title'] : GuildRanksAndClearance::RANK_INITIATE['title'];

        $discordApi->createMessageInChannel($officerChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedOfficerGroup . ': ' . $mentionedName . ' needs to have in-game guild rank of **' . $rankTitle . '**. Please promote/demote them accordingly!',
                    'tts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
        ]);
    }
}

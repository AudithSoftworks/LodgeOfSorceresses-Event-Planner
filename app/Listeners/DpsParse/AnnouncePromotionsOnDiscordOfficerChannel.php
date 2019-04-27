<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseApproved;
use App\Services\GuildRankAndClearance;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnnouncePromotionsOnDiscordOfficerChannel
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
     * @param \App\Events\DpsParse\DpsParseApproved $event
     *
     * @return bool
     */
    public function handle(DpsParseApproved $event): bool
    {
        /*------------------------------------
         | Prelim
         *-----------------------------------*/

        $officerChannelId = config('services.discord.channels.officer_hq');
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();
        $dpsParse->load(['owner']);
        /** @var \App\Models\User $parseAuthor */
        $parseAuthor = $dpsParse->owner()->first();
        if (!$parseAuthor) {
            throw new ModelNotFoundException('Parse author record not found!');
        }
        $playerClearance = app('guild.ranks.clearance')->calculateTopClearanceForUser($parseAuthor);

        /*--------------------------------------------
         | Parse author mention names parsed
         *-------------------------------------------*/

        $parseAuthor->load('linkedAccounts');
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        $mentionedName = $parseOwnersDiscordAccount ? '<@!' . $parseOwnersDiscordAccount->remote_id . '>' : $parseAuthor->name;

        $mentionedOfficerGroup = '<@&' . GuildRankAndClearance::RANK_MAGISTER_TEMPLI['discordRole'] . '>';

        /*------------------------------------
         | Post the announcement
         *-----------------------------------*/

        $rankTitle = $playerClearance ? GuildRankAndClearance::CLEARANCE_LEVELS[$playerClearance]['rank']['discordRole'] : GuildRankAndClearance::RANK_INITIATE['discordRole'];
        $discordApi = app('discord.api');
        $discordApi->createMessageInChannel($officerChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedOfficerGroup . ': ' . $mentionedName . ' needs to have in-game guild rank of ' . '<@&' . $rankTitle . '>' . '. Please promote/demote them accordingly!',
                    'tts' => false,
                ]),
            ]
        ]);

        return true;
    }
}

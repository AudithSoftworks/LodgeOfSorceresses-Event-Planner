<?php namespace App\Listeners\DpsParses;

use App\Events\DpsParses\DpsParseApproved;
use App\Models\EquipmentSet;
use App\Services\GuildRankAndClearance;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnnounceDpsApprovalOnDiscord
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
     * @param \App\Events\DpsParses\DpsParseApproved $event
     *
     * @return bool
     */
    public function handle(DpsParseApproved $event): bool
    {
        /*------------------------------------
         | Prelim
         *-----------------------------------*/

        $announcementsChannelId = config('services.discord.channels.announcements');
        $midgameDpsParsesChannelId = config('services.discord.channels.midgame_dps_parses');

        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();
        $dpsParse->load(['owner', 'character']);

        /** @var \App\Models\User $parseAuthor */
        $parseAuthor = $dpsParse->owner()->first();
        if (!$parseAuthor) {
            throw new ModelNotFoundException('Parse author record not found!');
        }
        $character = $dpsParse->character()->first();
        if (!$character) {
            throw new ModelNotFoundException('Character record not found!');
        }
        $topClearanceExisting = app('guild.ranks.clearance')->calculateTopClearance($parseAuthor);

        /*--------------------------------------------
         | Me & Parse author mention names parsed
         *-------------------------------------------*/

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $myDiscordAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        $myMentionedName = $myDiscordAccount ? '<@!' . $myDiscordAccount->remote_id . '>' : $me->name;

        $parseAuthor->load('linkedAccounts');
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        $mentionedName = $parseOwnersDiscordAccount ? '<@!' . $parseOwnersDiscordAccount->remote_id . '>' : $parseAuthor->name;

        $mentionedAnnouncementsChannel = '<#' . $announcementsChannelId . '>';

        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);

        /*------------------------------------
         | Delete earlier messages
         *-----------------------------------*/

        $discordApi = app('discord.api');
        $discordApi->deleteMessagesInChannel($midgameDpsParsesChannelId, $discordMessageIdsToDelete);

        /*------------------------------------
         | Post the announcement
         *-----------------------------------*/

        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](https://eso-sets.com/set/' . $set->id . ')';
        }
        $rankTitle = $topClearanceExisting ? GuildRankAndClearance::CLEARANCE_LEVELS[$topClearanceExisting]['rank']['discord_role'] : GuildRankAndClearance::RANK_INITIATE['discord_role'];
        $clearanceTitle = $topClearanceExisting ? GuildRankAndClearance::CLEARANCE_LEVELS[$topClearanceExisting]['title'] : null;
        $responseDecoded = $discordApi->createMessageInChannel($midgameDpsParsesChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ': DPS parse you submitted has been **approved** by ' . $myMentionedName
                        . "\nAs a result, you have been assigned to the rank of " . $rankTitle . '. Please also expect additional ' . $mentionedAnnouncementsChannel . "\n"
                        . 'Details regarding your parse is listed below. The original Discord post of Parse submit created earlier (which should be above), is deleted now to avoid duplicates.',
                    'tts' => false,
                    'embed' => [
                        'color' => 0x008800,
                        'thumbnail' => [
                            'url' => cloudinary_url('special/logo.png', [
                                'secure' => true,
                                'width' => 300,
                                'height' => 300,
                            ])
                        ],
                        'fields' => [
                            [
                                'name' => 'New Clearance',
                                'value' => '<@&' . $rankTitle . '>' . ($clearanceTitle ? ', cleared for ' . $clearanceTitle : ', no clearance') . '.',
                            ],
                            [
                                'name' => 'DPS Amount',
                                'value' => $dpsParse->dps_amount,
                            ],
                            [
                                'name' => 'Character',
                                'value' => $character->name,
                            ],
                            [
                                'name' => 'Role',
                                'value' => RoleTypes::getRoleName($character->role),
                            ],
                            [
                                'name' => 'Class',
                                'value' => ClassTypes::getClassName($character->class),
                            ],
                            [
                                'name' => 'Sets Used',
                                'value' => implode(', ', $gearSetsParsed),
                                'inline' => false,
                            ],
                        ],
                        'image' => [
                            'url' => cloudinary_url($dpsParse->parse_file_hash, [
                                'secure' => true,
                            ])
                        ],
                        'footer' => [
                            'text' => 'Sent via Lodge of Sorceresses Planner at: https://planner.lodgeofsorceresses.com'
                        ]
                    ],
                ]),
            ]
        ]);

        /*------------------------------------
         | React to the message
         *-----------------------------------*/

        $discordApi->reactToMessageInChannel($midgameDpsParsesChannelId, $responseDecoded['id'], '✅');

        return true;
    }

    /**
     * @param string $commaSeparatedSetIds
     *
     * @return \App\Models\EquipmentSet[]
     */
    private function getGearSets(string $commaSeparatedSetIds): iterable
    {
        return EquipmentSet::whereIn('id', explode(',', $commaSeparatedSetIds))->get();
    }
}

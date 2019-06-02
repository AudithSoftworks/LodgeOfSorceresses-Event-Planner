<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseApproved;
use App\Models\Set;
use App\Services\GuildRankAndClearance;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnnounceDpsApprovalOnDiscord
{
    private const TOPIC_URLS_CORE_GUIDELINES = 'https://lodgeofsorceresses.com/topic/5506-pve-raid-core-guidelines/';

    private const TOPIC_URLS_CORE_REQUIREMENTS = 'https://lodgeofsorceresses.com/topic/4887-pve-raid-core-requirements-to-join/';

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
        /** @var \App\Models\Character $character */
        $character = $dpsParse->character()->first();
        if (!$character) {
            throw new ModelNotFoundException('Character record not found!');
        }
        $playerClearance = app('guild.ranks.clearance')->calculateTopClearanceForUser($parseAuthor);
        $characterClearance = app('guild.ranks.clearance')->calculateTopClearanceForCharacter($character);

        /*--------------------------------------------
         | Me & Parse author mention names parsed
         *-------------------------------------------*/

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $myDiscordAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        $myMentionedName = $myDiscordAccount ? '<@!' . $myDiscordAccount->remote_id . '>' : $me->name;

        $parseAuthor->loadMissing('linkedAccounts');
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        if (!$parseOwnersDiscordAccount) {
            return false;
        }

        $mentionedName = '<@!' . $parseOwnersDiscordAccount->remote_id . '>';
        $mentionedAnnouncementsChannel = '<#' . $announcementsChannelId . '>';
        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);

        /*------------------------------------
         | Delete earlier messages
         *-----------------------------------*/

        $discordApi = app('discord.api');
        $discordApi->deleteMessagesInChannel($midgameDpsParsesChannelId, $discordMessageIdsToDelete);

        /*------------------------------------------------------
         | Post approval announcement in #dps-parses channel
         *-----------------------------------------------------*/

        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](https://eso-sets.com/set/' . $set->id . ')';
        }
        $rankTitle = $playerClearance ? GuildRankAndClearance::CLEARANCE_LEVELS[$playerClearance]['rank']['discordRole'] : GuildRankAndClearance::RANK_INITIATE['discordRole'];
        $playerClearanceTitle = $playerClearance ? GuildRankAndClearance::CLEARANCE_LEVELS[$playerClearance]['title'] : null;
        $characterClearanceTitle = $characterClearance ? GuildRankAndClearance::CLEARANCE_LEVELS[$characterClearance]['title'] : null;
        $className = ClassTypes::getClassName($character->class);
        $responseDecoded = $discordApi->createMessageInChannel($midgameDpsParsesChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ': DPS parse you submitted has been **approved** by ' . $myMentionedName . ".\n"
                        . ($characterClearanceTitle ? '**Your character is cleared for ' . $characterClearanceTitle . '.**' : '**You character wasn\'t cleared for any content!**')
                        . "\nYour current guild rank (based on all your cleared characters) is <@&" . $rankTitle . '>.'
                        . ($characterClearanceTitle ? "\nPlease also expect additional " . $mentionedAnnouncementsChannel : '')
                        . "\nDetails regarding your parse is listed below. The original Discord post of Parse submit created earlier (which should be above), is deleted now to avoid duplicates.",
                    'tts' => false,
                    'embed' => [
                        'color' => 0x00aa00,
                        'thumbnail' => [
                            'url' => cloudinary_url('special/logo.png', [
                                'secure' => true,
                                'width' => 300,
                                'height' => 300,
                            ])
                        ],
                        'fields' => [
                            [
                                'name' => 'Character Clearance',
                                'value' => ($characterClearanceTitle ? 'Cleared for ' . $characterClearanceTitle : 'No clearance') . '.',
                            ],
                            [
                                'name' => 'Updated Player Clearance',
                                'value' => '<@&' . $rankTitle . '>' . ($playerClearanceTitle ? ', cleared for ' . $playerClearanceTitle : ', no clearance') . '.',
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
                                'value' => $className,
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
                            'text' => 'Sent via Lodge of Sorceresses Planner at: ' . env('APP_URL')
                        ]
                    ],
                ]),
            ]
        ]);

        /*------------------------------------
         | React to the message
         *-----------------------------------*/

        $discordApi->reactToMessageInChannel($midgameDpsParsesChannelId, $responseDecoded['id'], '✅');

        /*-----------------------------------------------------------
         | Post clearance announcement as DM to the author
         *----------------------------------------------------------*/

        if ($characterClearanceTitle) {
            $roleName = RoleTypes::getShortRoleText($character->role);
            $dmChannel = $discordApi->createDmChannel($parseOwnersDiscordAccount->remote_id);
            $discordApi->createMessageInChannel($dmChannel['id'], [
                RequestOptions::FORM_PARAMS => [
                    'payload_json' => json_encode([
                        'content' => $mentionedName . ', you are cleared for **' . $characterClearanceTitle . '** on your ' . $className . ' ' . $roleName . ' named _' . $character->name . "_.\n"
                            . 'Please start doing content with folks and let the guild get to know you and vice versa (3 day event attendance is in effect). '
                            . 'It is very important for our community! '
                            . "Depending on your clearance, _Midgame_, _DPS Trainings_ & _Endgame_ are potentially eligible content towards your attendance.\n"
                            . 'Additionally, please do not forget to constantly improve and keep sending your improved DPS Parses at least every 15 days. Otherwise your Clearance might get revoked. '
                            . "Refer to our _Guidance team_, should you need any assistance with training or any other questions! Good luck!\n"
                            . '_P.S.:_ Feel free to find important links listed below for your convenience.'
                        ,
                        'tts' => false,
                        'embed' => [
                            'color' => 0x00aa00,
                            'thumbnail' => [
                                'url' => cloudinary_url('special/logo.png', [
                                    'secure' => true,
                                    'width' => 300,
                                    'height' => 300,
                                ])
                            ],
                            'fields' => [
                                [
                                    'name' => 'DPS Training',
                                    'value' => 'Sign-up [on Calendar](https://lodgeofsorceresses.com/calendar/)',
                                ],
                                [
                                    'name' => 'Raid Core Guidelines',
                                    'value' => 'Read [this topic](' . self::TOPIC_URLS_CORE_GUIDELINES . ') as a preparation to join a Core',
                                ],
                                [
                                    'name' => 'Raid Core Requirements to Join',
                                    'value' => 'Read [this topic](' . self::TOPIC_URLS_CORE_REQUIREMENTS . ') as a preparation to join a Core',
                                ],
                            ],
                            'footer' => [
                                'text' => 'Sent via Lodge of Sorceresses Planner at: ' . env('APP_URL')
                            ]
                        ],
                    ]),
                ]
            ]);
        }

        return true;
    }

    /**
     * @param string $commaSeparatedSetIds
     *
     * @return \App\Models\Set[]
     */
    private function getGearSets(string $commaSeparatedSetIds): iterable
    {
        return Set::whereIn('id', explode(',', $commaSeparatedSetIds))->get();
    }
}

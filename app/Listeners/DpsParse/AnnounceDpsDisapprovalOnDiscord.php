<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseDisapproved;
use App\Models\EquipmentSet;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnnounceDpsDisapprovalOnDiscord
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
     * @param \App\Events\DpsParse\DpsParseDisapproved $event
     *
     * @return bool
     */
    public function handle(DpsParseDisapproved $event): bool
    {
        /*------------------------------------
         | Prelim
         *-----------------------------------*/

        $channelId = config('services.discord.channels.midgame_dps_parses');

        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();
        $dpsParse->load(['owner', 'character']);

        $parseAuthor = $dpsParse->owner()->first();
        if (!$parseAuthor) {
            throw new ModelNotFoundException('Parse author record not found!');
        }
        $character = $dpsParse->character()->first();
        if (!$character) {
            throw new ModelNotFoundException('Character record not found!');
        }

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

        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);

        /*------------------------------------
         | Delete earlier messages
         *-----------------------------------*/

        $discordApi = app('discord.api');
        $discordApi->deleteMessagesInChannel($channelId, $discordMessageIdsToDelete);

        /*------------------------------------
         | Post the announcement
         *-----------------------------------*/

        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](https://eso-sets.com/set/' . $set->id . ')';
        }
        $responseDecoded = $discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ': DPS parse you submitted has been **disapproved** by ' . $myMentionedName
                        . "\nPlease fix your parse and re-submit! DPS Parse rules can be found here: https://lodgeofsorceresses.com/topic/5158-pve-raid-core-dps-parse-rules/\n"
                        . 'Details regarding your parse is listed below. The original Discord post of Parse submit created earlier (which should be above), is deleted now to avoid duplicates.',
                    'tts' => false,
                    'embed' => [
                        'color' => 0x880000,
                        'thumbnail' => [
                            'url' => cloudinary_url('special/logo.png', [
                                'secure' => true,
                                'width' => 300,
                                'height' => 300,
                            ])
                        ],
                        'fields' => [
                            [
                                'name' => 'Reason for Disapproval',
                                'value' => $dpsParse->reason_for_disapproval,
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
         | React with :x:
         *-----------------------------------*/

        $discordApi->reactToMessageInChannel($channelId, $responseDecoded['id'], '❌');

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
<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseDisapproved;
use App\Models\Set;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Cloudinary;
use GuzzleHttp\RequestOptions;

class AnnounceDpsDisapprovalOnDiscord
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
     * @param \App\Events\DpsParse\DpsParseDisapproved $event
     *
     * @throws \JsonException
     * @return bool
     */
    public function handle(DpsParseDisapproved $event): bool
    {
        /*------------------------------------
         | Prelim
         *-----------------------------------*/

        $channelId = config('services.discord.channels.dps_parses_logs');
        $dpsParse = $event->getDpsParse();
        if (($parseAuthor = $event->getOwner()) === null) {
            return false;
        }
        $character = $event->getCharacter();

        /*--------------------------------------------
         | Me & Parse author mention names parsed
         *-------------------------------------------*/

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $myDiscordAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        $myMentionedName = $myDiscordAccount ? '<@!' . $myDiscordAccount->remote_id . '>' : $me->name;

        $parseAuthor->loadMissing('linkedAccounts');
        $parseOwnersDiscordAccount = $parseAuthor->linkedAccounts()->where('remote_provider', 'discord')->first();
        $mentionedName = $parseAuthor->name;
        if ($parseOwnersDiscordAccount) {
            $mentionedName = '<@!' . $parseOwnersDiscordAccount->remote_id . '>';
        }

        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);

        /*------------------------------------
         | Delete earlier messages
         *-----------------------------------*/

        $discordApi = app('discord.api');
        $discordApi->deleteMessagesInChannel($channelId, $discordMessageIdsToDelete);

        /*------------------------------------
         | Post as #announcements & DM
         *-----------------------------------*/

        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](https://eso-sets.com/set/' . $set->id . ')';
        }
        $message = [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ': DPS parse you submitted has been **disapproved** by ' . $myMentionedName . "\n"
                        . 'Please fix your parse and re-submit! Details regarding your parse is listed below. '
                        . 'The original Discord post of Parse submit created earlier (which should be above), is deleted now to avoid duplicates.',
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
                                'value' => sprintf('[%s](https://planner.lodgeofsorceresses.com/characters/%s)', $character->name, $character->id),
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
                            'text' => 'Sent via Lodge of Sorceresses Guild Planner at: https://planner.lodgeofsorceresses.com'
                        ]
                    ],
                ], JSON_THROW_ON_ERROR),
            ]
        ];
        $responseDecoded = $discordApi->createMessageInChannel($channelId, $message);
        $dpsParse->discord_notification_message_ids = $responseDecoded['id'];
        $dpsParse->save();

        $dmChannel = $discordApi->createDmChannel($parseOwnersDiscordAccount->remote_id);
        $discordApi->createMessageInChannel($dmChannel['id'], $message);

        /*------------------------------------
         | React with :x:
         *-----------------------------------*/

        $discordApi->reactToMessageInChannel($channelId, $responseDecoded['id'], '❌');

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

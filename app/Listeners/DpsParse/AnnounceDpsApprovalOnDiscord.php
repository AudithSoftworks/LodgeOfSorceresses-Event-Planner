<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseApproved;
use App\Models\Set;
use App\Services\GuildRanksAndClearance;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Cloudinary;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;

class AnnounceDpsApprovalOnDiscord
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
     * @param \App\Events\DpsParse\DpsParseApproved $event
     *
     * @throws \JsonException
     * @return bool
     */
    public function handle(DpsParseApproved $event): bool
    {
        /*-------------
         | Prelim
         *------------*/

        $dpsParsesChannelId = config('services.discord.channels.dps_parses_logs');
        $dpsParse = $event->getDpsParse();
        if (($parseAuthor = $event->getOwner()) === null) {
            return false;
        }
        $character = $event->getCharacter()->refresh();
        $playerClearance = app('guild.ranks.clearance')->calculateClearanceLevelOfUser($parseAuthor);
        $characterClearance = $character->approved_for_tier;

        /*--------------------------------------------
         | Me & Parse author mention names parsed
         *-------------------------------------------*/

        /** @var \App\Models\User $me */
        $me = Auth::user();
        /** @var \App\Models\UserOAuth $myDiscordAccount */
        $myDiscordAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        $myMentionedName = $myDiscordAccount ? '<@!' . $myDiscordAccount->remote_id . '>' : $me->name;

        $parseAuthor->loadMissing('linkedAccounts');
        /** @var \App\Models\UserOAuth $parseOwnersDiscordAccount */
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
        $discordApi->deleteMessagesInChannel($dpsParsesChannelId, $discordMessageIdsToDelete);

        /*------------------------------------------------------
         | Post approval announcement in #dps-parses channel
         *-----------------------------------------------------*/

        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](https://eso-sets.com/set/' . $set->id . ')';
        }
        $rankTitle = $playerClearance ? GuildRanksAndClearance::CLEARANCE_LEVELS[$playerClearance]['rank']['title'] : GuildRanksAndClearance::RANK_INITIATE['title'];
        $playerClearanceTitle = $playerClearance ? GuildRanksAndClearance::CLEARANCE_LEVELS[$playerClearance]['title'] : null;
        $characterClearanceTitle = $characterClearance ? GuildRanksAndClearance::CLEARANCE_LEVELS[$characterClearance]['title'] : null;
        $className = ClassTypes::getClassName($character->class);
        $responseDecoded = $discordApi->createMessageInChannel($dpsParsesChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ': DPS parse you submitted has been **approved** by ' . $myMentionedName . ".\n"
                        . ($characterClearanceTitle ? '**Your character is cleared for ' . $characterClearanceTitle . '.**' : '**You character wasn\'t cleared for any content!**')
                        . "\nYour current guild rank (based on all your cleared characters) is **" . $rankTitle . '**.'
                        . ($characterClearanceTitle ? "\nPlease also expect a DM from our bot with additional information." : '')
                        . "\nDetails regarding your parse is listed below. The original Discord post of Parse submit created earlier (which should be above), is deleted now to avoid duplicates.",
                    'tts' => false,
                    'embed' => [
                        'color' => 0x00aa00,
                        'thumbnail' => [
                            'url' => cloudinary_url('special/logo.png', [
                                'secure' => true,
                                'width' => 300,
                                'height' => 300,
                            ]),
                        ],
                        'fields' => [
                            [
                                'name' => 'Character Clearance',
                                'value' => ($characterClearanceTitle ? 'Cleared for ' . $characterClearanceTitle : 'No clearance') . '.',
                            ],
                            [
                                'name' => 'Updated Player Clearance',
                                'value' => '_' . $rankTitle . '_' . ($playerClearanceTitle ? ', cleared for ' . $playerClearanceTitle : ', no clearance') . '.',
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
                            ]),
                        ],
                        'footer' => [
                            'text' => 'Sent via Lodge of Sorceresses Guild Planner at: https://planner.lodgeofsorceresses.com',
                        ],
                    ],
                ], JSON_THROW_ON_ERROR),
            ],
        ]);
        $dpsParse->discord_notification_message_ids = $responseDecoded['id'];
        $dpsParse->save();

        /*------------------------------------
         | React to the message
         *-----------------------------------*/

        $discordApi->reactToMessageInChannel($dpsParsesChannelId, $responseDecoded['id'], '✅');

        /*-----------------------------------------------------------
         | Post clearance announcement as DM to the author
         *----------------------------------------------------------*/

        if ($characterClearanceTitle) {
            $roleName = RoleTypes::getShortRoleText($character->role);
            $dmChannel = $discordApi->createDmChannel($parseOwnersDiscordAccount->remote_id);
            $discordApi->createMessageInChannel($dmChannel['id'], [
                RequestOptions::FORM_PARAMS => [
                    'payload_json' => json_encode([
                        'content' => sprintf(
                            '%s, you are cleared for **%s** on your _%s (%s)_ named _%s_.' . PHP_EOL
                            . 'Please start doing content with folks and let the community get to know you and vice versa. '
                            . 'Additionally, please keep training and submit your improved Parses every 60 days at latest. '
                            . 'Otherwise your character\'s Tier-level might get revoked. Good luck!',
                            $mentionedName,
                            $characterClearanceTitle,
                            $className,
                            $roleName,
                            $character->name
                        ),
                    ], JSON_THROW_ON_ERROR),
                ],
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

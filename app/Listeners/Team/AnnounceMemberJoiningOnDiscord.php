<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberJoined;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use GuzzleHttp\RequestOptions;

class AnnounceMemberJoiningOnDiscord
{
    /**
     * @param \App\Events\Team\MemberJoined $event
     *
     * @return bool
     */
    public function handle(MemberJoined $event): bool
    {
        /*-------------
         | Prelim
         *------------*/

        $team = $event->getTeam();
        $character = $event->getCharacter();

        $pveCoreAnnouncementsChannelId = config('services.discord.channels.pve_core_announcements');
        $dpsParsesLogsChannelId = config('services.discord.channels.dps_parses_logs');
        $teamMentionName = '<@&' . $team->discord_id . '>';
        $member = $character->owner;
        /** @var \App\Models\UserOAuth $membersDiscordAccount */
        $membersDiscordAccount = $member->linkedAccounts()->where('remote_provider', 'discord')->first();
        $memberMentionName = $membersDiscordAccount ? '<@!' . $membersDiscordAccount->remote_id . '>' : $member->name;

        $discordApi = app('discord.api');

        /*--------------------------------------------------------------------------------------------------
         | Post removal announcement in PvE-Cores#announcements & #dps-parses-logs channels
         *-------------------------------------------------------------------------------------------------*/

        $discordApi->createMessageInChannel($pveCoreAnnouncementsChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => sprintf(
                        '%s\'s character _%s_ has **joined** %s as _%s_ / _%s_. Please welcome him/her, folks! Good luck, %s!',
                        $memberMentionName,
                        $character->name,
                        $teamMentionName,
                        ClassTypes::getClassName($character->class),
                        RoleTypes::getShortRoleText($character->role),
                        $memberMentionName
                    ),
                    'tts' => false,
                ]),
            ]
        ]);

        $discordApi->createMessageInChannel($dpsParsesLogsChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => sprintf(
                        '%s\'s character _%s_ has **joined** %s.',
                        $memberMentionName,
                        $character->name,
                        $teamMentionName
                    ),
                    'tts' => false,
                ]),
            ]
        ]);

        return true;
    }
}

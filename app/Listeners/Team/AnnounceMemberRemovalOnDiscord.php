<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberRemoved;
use GuzzleHttp\RequestOptions;

class AnnounceMemberRemovalOnDiscord
{
    /**
     * @param \App\Events\Team\MemberRemoved $event
     *
     * @return bool
     */
    public function handle(MemberRemoved $event): bool
    {
        /*-------------
         | Prelim
         *------------*/

        $team = $event->getTeam();
        $character = $event->getCharacter();

        $pveCoreAnnouncementsChannelId = config('services.discord.channels.pve_core_announcements');
        $teamMentionName = '<@&' . $team->discord_id . '>';
        $member = $character->owner;
        /** @var \App\Models\UserOAuth $membersDiscordAccount */
        $membersDiscordAccount = $member->linkedAccounts()->where('remote_provider', 'discord')->first();
        $memberMentionName = $membersDiscordAccount ? '<@!' . $membersDiscordAccount->remote_id . '>' : $member->name;

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $myDiscordAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();
        $myMentionName = $myDiscordAccount ? '<@!' . $myDiscordAccount->remote_id . '>' : $me->name;

        $discordApi = app('discord.api');

        /*--------------------------------------------------------------------------------------------------
         | Post removal announcement in PvE-Cores#announcements
         *-------------------------------------------------------------------------------------------------*/

        $discordApi->createMessageInChannel($pveCoreAnnouncementsChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => sprintf(
                        '%s\'s character _%s_ has been **removed** from %s by %s. **Note**: %s may still be part of the team with other characters.',
                        $memberMentionName,
                        $character->name,
                        $teamMentionName,
                        $myMentionName,
                        $memberMentionName
                    ),
                    'tts' => false,
                ]),
            ]
        ]);

        /*-----------------------------------------------------------
         | Post removal announcement as DM to the member
         *----------------------------------------------------------*/

        $dmChannel = $discordApi->createDmChannel($membersDiscordAccount->remote_id);
        $discordApi->createMessageInChannel($dmChannel['id'], [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => sprintf(
                        '%s, your character _%s_ has been **removed** from %s by _%s_. If you are unsure how this happened, please contact the Team Leader!',
                        $memberMentionName,
                        $character->name,
                        $team->name,
                        $me->name
                    ),
                    'tts' => false,
                ]),
            ]
        ]);

        return true;
    }
}

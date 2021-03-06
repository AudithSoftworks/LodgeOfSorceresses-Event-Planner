<?php

namespace App\Listeners\Team;

use App\Events\Team\MemberInvited;
use App\Models\Character;
use GuzzleHttp\RequestOptions;

class DmMemberUponInvitationOnDiscord
{
    /**
     * @param \App\Events\Team\MemberInvited $event
     *
     * @return bool
     */
    public function handle(MemberInvited $event): bool
    {
        /*-------------
         | Prelim
         *------------*/

        $team = $event->getTeam();
        $character = $event->getCharacter();

        $member = $character->owner;
        /** @var \App\Models\UserOAuth $membersDiscordAccount */
        $membersDiscordAccount = $member->linkedAccounts()->where('remote_provider', 'discord')->first();
        $memberMentionName = $membersDiscordAccount ? '<@!' . $membersDiscordAccount->remote_id . '>' : $member->name;
        $invitationLink = sprintf(config('app.url') . '/teams/%d/characters/%d', $team->id, $character->id);
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $discordApi = app('discord.api');

        $dmChannel = $discordApi->createDmChannel($membersDiscordAccount->remote_id);
        $discordApi->createMessageInChannel($dmChannel['id'], [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => sprintf(
                        '%s, your character _%s_ has been **invited** to join %s by _%s_. Please click here: <%s> to review and possibly accept/decline the invitation.',
                        $memberMentionName,
                        $character->name,
                        $team->name,
                        $me->name,
                        $invitationLink
                    ),
                    'tts' => false,
                ]),
            ]
        ]);

        return true;
    }
}

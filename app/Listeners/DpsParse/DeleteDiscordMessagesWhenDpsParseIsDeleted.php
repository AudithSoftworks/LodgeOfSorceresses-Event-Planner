<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseDeleted;

class DeleteDiscordMessagesWhenDpsParseIsDeleted
{
    /**
     * @param \App\Events\DpsParse\DpsParseDeleted $event
     *
     * @return bool
     */
    public function handle(DpsParseDeleted $event): bool
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();
        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);
        $response = app('discord.api')->deleteMessagesInChannel(config('services.discord.channels.midgame_dps_parses'), $discordMessageIdsToDelete);
        if ($response) {
            $dpsParse->forceDelete();
        }

        return true;
    }
}

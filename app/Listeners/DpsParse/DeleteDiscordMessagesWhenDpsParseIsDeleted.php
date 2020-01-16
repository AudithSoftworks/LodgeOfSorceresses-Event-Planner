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
        $dpsParse = $event->getDpsParse();
        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);
        $response = app('discord.api')->deleteMessagesInChannel(config('services.discord.channels.dps_parses_logs'), $discordMessageIdsToDelete);
        if ($response) {
            $dpsParse->forceDelete();
        }

        return true;
    }
}

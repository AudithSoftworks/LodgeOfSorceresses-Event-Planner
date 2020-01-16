<?php namespace App\Listeners\DpsParse;

use App\Events\Character\CharacterDeleting;

class DeleteDiscordMessagesWhenCharacterIsDeleting
{
    /**
     * @param \App\Events\Character\CharacterDeleting $event
     *
     * @return bool
     */
    public function handle(CharacterDeleting $event): bool
    {
        $dpsParses = $event->getDpsParses();
        foreach ($dpsParses as $dpsParse) {
            $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);
            $response = app('discord.api')->deleteMessagesInChannel(config('services.discord.channels.dps_parses_logs'), $discordMessageIdsToDelete);
            if ($response) {
                $dpsParse->forceDelete();
            }
        }

        return true;
    }
}

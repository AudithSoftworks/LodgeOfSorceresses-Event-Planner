<?php namespace App\Listeners\DpsParse;

use App\Events\Character\CharacterDeleting;

class DeleteDpsParsesOfDeletingCharacter
{
    /**
     * @param \App\Events\Character\CharacterDeleting $event
     *
     * @return bool
     */
    public function handle(CharacterDeleting $event): bool
    {
        $character = $event->character->loadMissing('dpsParses');
        $dpsParses = $character->dpsParses()->get();
        foreach ($dpsParses as $dpsParse) {
            $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);
            $response = app('discord.api')->deleteMessagesInChannel(config('services.discord.channels.midgame_dps_parses'), $discordMessageIdsToDelete);
            if ($response) {
                $dpsParse->forceDelete();
            }
        }

        return true;
    }
}

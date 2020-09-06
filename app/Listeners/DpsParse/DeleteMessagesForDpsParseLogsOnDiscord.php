<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\GetDpsParseInterface;
use App\Events\DpsParse\GetDpsParsesInterface;
use GuzzleHttp\Exception\BadResponseException;

class DeleteMessagesForDpsParseLogsOnDiscord
{
    /**
     * @param \App\Events\DpsParse\GetDpsParseInterface|\App\Events\DpsParse\GetDpsParsesInterface $event
     *
     * @return bool
     */
    public function handle($event): bool
    {
        $dpsParses = [];
        if ($event instanceof GetDpsParseInterface) {
            $dpsParses = collect($event->getDpsParse());
        } elseif ($event instanceof GetDpsParsesInterface) {
            $dpsParses = $event->getDpsParses();
        }

        foreach ($dpsParses as $dpsParse) {
            $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);
            try {
                $response = app('discord.api')->deleteMessagesInChannel(config('services.discord.channels.dps_parses_logs'), $discordMessageIdsToDelete);
                if ($response) {
                    $dpsParse->forceDelete();
                }
            } catch (BadResponseException $e) {
                // We capture some exceptions in DiscordApi service, but if something still propagates here, better restore the Parse.
                $dpsParse->restore();

                throw $e;
            }
        }

        return true;
    }
}

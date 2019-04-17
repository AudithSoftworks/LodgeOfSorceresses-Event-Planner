<?php namespace App\Listeners\DpsParses;

use App\Events\DpsParses\DpsParseDeleted;
use GuzzleHttp\Client;

class DeleteDiscordMessagesWhenDpsParseIsDeleted
{
    private const DISCORD_API_ENDPOINT = 'https://discordapp.com/api/';

    /**
     * @var \Illuminate\Config\Repository
     */
    private $discordChannels;

    /**
     * @var \GuzzleHttp\Client
     */
    private $discordClient;

    public function __construct()
    {
        $this->discordChannels = config('services.discord.channels');

        $botAccessToken = config('services.discord.bot_token');
        $this->discordClient = new Client([
            'base_uri' => self::DISCORD_API_ENDPOINT,
            'headers' => [
                'Authorization' => 'Bot ' . $botAccessToken,
                'Content-Type' => 'application/json'
            ],
        ]);
    }

    /**
     * @param \App\Events\DpsParses\DpsParseDeleted $event
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

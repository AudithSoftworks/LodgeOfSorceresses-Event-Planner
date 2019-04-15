<?php namespace App\Listeners\DpsParses;

use App\Events\DpsParses\DpsParseDeleted;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

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
        if (count($discordMessageIdsToDelete) > 1) {
            $response = $this->discordClient->post('channels/' . $this->discordChannels['midgame_dps_parses'] . '/messages/bulk-delete', [
                RequestOptions::JSON => [
                    'messages' => $discordMessageIdsToDelete,
                ]
            ]);
        } else {
            $response = $this->discordClient->delete('channels/' . $this->discordChannels['midgame_dps_parses'] . '/messages/' . $dpsParse->discord_notification_message_ids);
        }
        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
            $dpsParse->forceDelete();
        }

        return true;
    }
}

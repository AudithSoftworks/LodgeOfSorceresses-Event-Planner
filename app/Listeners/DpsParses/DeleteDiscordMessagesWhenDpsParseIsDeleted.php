<?php namespace App\Listeners\DpsParses;

use App\Events\DpsParses\DpsParseDeleted;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

class DeleteDiscordMessagesWhenDpsParseIsDeleted
{
    private const DISCORD_API_ENDPOINT = 'https://discordapp.com/api/';

    private const DISCORD_MIDGAME_DPS_PARSES_CHANNEL_ID = '460038712311545856';

    private const DISCORD_CORE_DPS_PARSES_CHANNEL_ID = '496635762855641090';

    private const DISCORD_TEST_CHANNEL_ID = '551378145500987392';

    /**
     * @param \App\Events\DpsParses\DpsParseDeleted $event
     *
     * @return bool
     */
    public function handle(DpsParseDeleted $event): bool
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();

        $botAccessToken = config('services.discord.bot_token');
        $discordClient = new Client([
            'base_uri' => self::DISCORD_API_ENDPOINT,
            'headers' => [
                'Authorization' => 'Bot ' . $botAccessToken,
                'Content-Type' => 'application/json'
            ],
        ]);

        $discordMessageIdsToDelete = explode(',', $dpsParse->discord_notification_message_ids);
        if (count($discordMessageIdsToDelete) > 1) {
            $response = $discordClient->post('channels/' . self::DISCORD_TEST_CHANNEL_ID . '/messages/bulk-delete', [
                RequestOptions::JSON => [
                    'messages' => $discordMessageIdsToDelete,
                ]
            ]);
        } else {
            $response = $discordClient->delete('channels/' . self::DISCORD_TEST_CHANNEL_ID . '/messages/' . $dpsParse->discord_notification_message_ids);
        }
        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
            $dpsParse->forceDelete();
        }

        return true;
    }
}

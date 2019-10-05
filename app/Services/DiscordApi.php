<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

class DiscordApi extends AbstractApi
{
    public const ROLE_SOULSHRIVEN = '499970844928507906';

    public const ROLE_NEOPHYTE_SHRIVEN = '591367441297309703';

    public const ROLE_PRACTICUS_SHRIVEN = '591367589540659200';

    public const ROLE_ADEPTUS_MINOR_SHRIVEN = '591367585694613506';

    public const ROLE_ADEPTUS_MAJOR_SHRIVEN = '591367721565028392';

    public const ROLE_MEMBERS = '230086132799504394';

    public const ROLE_INITIATE = '465486256810360832';

    public const ROLE_NEOPHYTE = '465486430932566017';

    public const ROLE_PRACTICUS = '465486736269639680';

    public const ROLE_ADEPTUS_MINOR = '479643035639087106';

    public const ROLE_ADEPTUS_MAJOR = '460567365038637056';

    public const ROLE_DOMINUS_LIMINIS = '479642604598722573';

    public const ROLE_RECTOR = '534135978093182977';

    public const ROLE_MAGISTER_TEMPLI = '230086456943706113';

    public const ROLE_GUIDANCE = '499972678526959617';

    public const ROLE_RAID_LEADERS = '499973058401140737';

    public const ROLE_HEALER = '452549590911025154';

    public const ROLE_TANK = '452549392189358111';

    public const ROLE_DAMAGE_DEALER = '452549771283005440';

    public const ROLE_CORE_ONE = '491244517589254146';

    public const ROLE_CORE_TWO = '491244828680519680';

    public const ROLE_CORE_THREE = '531557176586797067';

    private const DISCORD_API_ENDPOINT = 'https://discordapp.com/api/';

    /**
     * @var \GuzzleHttp\Client
     */
    private $discordClient;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $discordGuildId;

    public function __construct()
    {
        $botAccessToken = config('services.discord.bot_token');
        $this->discordClient = new Client([
            'base_uri' => self::DISCORD_API_ENDPOINT,
            'headers' => [
                'Authorization' => 'Bot ' . $botAccessToken,
                'Content-Type' => 'application/json'
            ],
        ]);
        $this->discordGuildId = config('services.discord.guild_id');
    }

    public function createMessageInChannel(string $channelId, array $params): array
    {
        return $this->executeCallback(function (string $channelId, array $params) {
            $response = $this->discordClient->post('channels/' . $channelId . '/messages', $params);

            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        }, $channelId, $params);
    }

    public function deleteMessagesInChannel(string $channelId, array $messageIds): bool
    {
        $return = $this->executeCallback(function (string $channelId, array $messageIds) {
            if (!count($messageIds)) {
                return false;
            }
            if (count($messageIds) > 1) {
                $response = $this->discordClient->post('channels/' . $channelId . '/messages/bulk-delete', [
                    RequestOptions::JSON => [
                        'messages' => $messageIds,
                    ]
                ]);
            } else {
                $response = $this->discordClient->delete('channels/' . $channelId . '/messages/' . $messageIds[0]);
            }

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        }, $channelId, $messageIds);

        return $return ?? false;
    }

    public function reactToMessageInChannel(string $channelId, string $messageId, string $reaction): bool
    {
        return $this->executeCallback(function (string $channelId, string $messageId, string $reaction) {
            $response = $this->discordClient->put('channels/' . $channelId . '/messages/' . $messageId . '/reactions/' . $reaction . '/@me');

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        }, $channelId, $messageId, $reaction);
    }

    public function getGuildMember(string $memberId): ?array
    {
        return $this->executeCallback(function (string $memberId) {
            $response = $this->discordClient->get('guilds/' . $this->discordGuildId . '/members/' . $memberId);

            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        }, $memberId);
    }

    public function modifyGuildMember(string $memberId, array $params): bool
    {
        return $this->executeCallback(function (string $memberId, array $params) {
            $response = $this->discordClient->patch('guilds/' . $this->discordGuildId . '/members/' . $memberId, [
                RequestOptions::JSON => $params
            ]);

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        }, $memberId, $params);
    }

    public function createDmChannel(string $recipientId): array
    {
        return $this->executeCallback(function (string $recipientId) {
            $response = $this->discordClient->post('users/@me/channels', [
                RequestOptions::JSON => [
                    'recipient_id' => $recipientId,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        }, $recipientId);
    }

    public function getGuildRoles(): array
    {
        return $this->executeCallback(function () {
            $response = $this->discordClient->get('guilds/' . $this->discordGuildId . '/roles');

            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        });
    }
}

<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

class DiscordApi
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

    /**
     * @var array
     */
    private $lastResponseHeaders = [];

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

    public function getLastResponseHeaders(): array
    {
        return $this->lastResponseHeaders;
    }

    public function createMessageInChannel(string $channelId, array $params): array
    {
        $response = $this->discordClient->post('channels/' . $channelId . '/messages', $params);
        $this->lastResponseHeaders = $response->getHeaders();

        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
    }

    public function deleteMessagesInChannel(string $channelId, array $messageIds): bool
    {
        try {
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
            $this->lastResponseHeaders = $response->getHeaders();

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        } catch (RequestException $e) {
            return false;
        }
    }

    public function reactToMessageInChannel(string $channelId, string $messageId, string $reaction): bool
    {
        $response = $this->discordClient->put('channels/' . $channelId . '/messages/' . $messageId . '/reactions/' . $reaction . '/@me');
        $this->lastResponseHeaders = $response->getHeaders();

        return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
    }

    public function getGuildMember(string $memberId): ?array
    {
        $response = $this->discordClient->get('guilds/' . $this->discordGuildId . '/members/' . $memberId);
        $this->lastResponseHeaders = $response->getHeaders();

        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
    }

    public function modifyGuildMember(string $memberId, array $params): bool
    {
        $response = $this->discordClient->patch('guilds/' . $this->discordGuildId . '/members/' . $memberId, [
            RequestOptions::JSON => $params
        ]);
        $this->lastResponseHeaders = $response->getHeaders();

        return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
    }

    public function createDmChannel(string $recipientId): array
    {
        $response = $this->discordClient->post('users/@me/channels', [
            RequestOptions::JSON => [
                'recipient_id' => $recipientId,
            ]
        ]);
        $this->lastResponseHeaders = $response->getHeaders();

        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
    }

    public function getGuildRoles(): array
    {
        $response = $this->discordClient->get('guilds/' . $this->discordGuildId . '/roles');
        $this->lastResponseHeaders = $response->getHeaders();

        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
    }
}

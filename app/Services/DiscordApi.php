<?php

namespace App\Services;

use App\Models\UserOAuth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

class DiscordApi extends AbstractApi
{
    public const ROLE_SOULSHRIVEN = '499970844928507906';

    public const ROLE_INITIATE_SHRIVEN = '592359837430579246';

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

    public const ROLE_ADEPTUS_EXEMPTUS = '534135978093182977';

    public const ROLE_MAGISTER_TEMPLI = '230086456943706113';

    public const ROLE_GUIDANCE = '499972678526959617';

    public const ROLE_RAID_LEADERS = '499973058401140737';

    public const ROLE_HEALER = '452549590911025154';

    public const ROLE_TANK = '452549392189358111';

    public const ROLE_DAMAGE_DEALER = '452549771283005440';

    /**
     * @var string
     */
    private $discordGuildId;

    public function __construct()
    {
        parent::__construct('discord');
        $this->apiUrl = 'https://discordapp.com/api/';
        $this->discordGuildId = config('services.discord.guild_id');
    }

    protected function getApiClient(): Client
    {
        if ($this->apiClient !== null) {
            return $this->apiClient;
        }

        $botAccessToken = config('services.discord.bot_token');

        return $this->createHttpClient([
            'headers' => [
                'Authorization' => 'Bot ' . $botAccessToken,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    protected function getOauthClient(): Client
    {
        if ($this->oauthClient !== null) {
            return $this->oauthClient;
        }

        $token = $this->getToken();

        return $this->oauthClient = $this->createHttpClient([
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    }

    public function createMessageInChannel(string $channelId, array $params): ?array
    {
        return $this->executeCallback(function (string $channelId, array $params) {
            $response = $this->getApiClient()->post('channels/' . $channelId . '/messages', $params);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $channelId, $params);
    }

    public function getChannelMessages(string $channelId, array $params = []): ?array
    {
        return $this->executeCallback(function (string $channelId, array $params) {
            $response = $this->getApiClient()->get('channels/' . $channelId . '/messages', [
                RequestOptions::QUERY => [
                    'limit' => 100,
                ] + $params
            ]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $channelId, $params);
    }

    public function deleteMessagesInChannel(string $channelId, array $messageIds): ?bool
    {
        $return = $this->executeCallback(function (string $channelId, array $messageIds) {
            if (!count($messageIds)) {
                return false;
            }
            if (count($messageIds) > 1) {
                $response = $this->getApiClient()->post('channels/' . $channelId . '/messages/bulk-delete', [
                    RequestOptions::JSON => [
                        'messages' => $messageIds,
                    ]
                ]);
            } else {
                $response = $this->getApiClient()->delete('channels/' . $channelId . '/messages/' . $messageIds[0]);
            }

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        }, $channelId, $messageIds);

        return $return ?? false;
    }

    public function reactToMessageInChannel(string $channelId, string $messageId, string $reaction): ?bool
    {
        return $this->executeCallback(function (string $channelId, string $messageId, string $reaction) {
            $response = $this->getApiClient()->put('channels/' . $channelId . '/messages/' . $messageId . '/reactions/' . $reaction . '/@me');

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        }, $channelId, $messageId, $reaction);
    }

    public function getGuildMember(string $memberId): ?array
    {
        return $this->executeCallback(function (string $memberId) {
            $response = $this->getApiClient()->get('guilds/' . $this->discordGuildId . '/members/' . $memberId);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $memberId);
    }

    public function modifyGuildMember(string $memberId, array $params): ?bool
    {
        return $this->executeCallback(function (string $memberId, array $params) {
            $response = $this->getApiClient()->patch('guilds/' . $this->discordGuildId . '/members/' . $memberId, [
                RequestOptions::JSON => $params
            ]);

            return $response->getStatusCode() === Response::HTTP_NO_CONTENT;
        }, $memberId, $params);
    }

    public function createDmChannel(string $recipientId): ?array
    {
        return $this->executeCallback(function (string $recipientId) {
            $response = $this->getApiClient()->post('users/@me/channels', [
                RequestOptions::JSON => [
                    'recipient_id' => $recipientId,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $recipientId);
    }

    public function getGuildChannels(): ?array
    {
        return $this->executeCallback(function () {
            $response = $this->getApiClient()->get('guilds/' . $this->discordGuildId . '/channels');

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        });
    }

    public function getGuildRoles(): ?array
    {
        return $this->executeCallback(function () {
            $response = $this->getApiClient()->get('guilds/' . $this->discordGuildId . '/roles');

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        });
    }

    /**
     * @param \App\Models\UserOAuth $oauthAccount
     *
     * @throws \Exception
     */
    protected function refreshToken(UserOAuth $oauthAccount): void
    {
        $clientId = config('services.discord.client_id');
        $clientSecret = config('services.discord.client_secret');
        $redirectUri = config('services.discord.redirect');
        $httpClient = $this->createHttpClient();
        $response = $httpClient->post('oauth2/token', [
            RequestOptions::FORM_PARAMS => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'refresh_token',
                'refresh_token' => $oauthAccount->refresh_token,
                'scope' => 'identify email',
            ],
        ]);
        $responseBody = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $oauthAccount->token = $responseBody['token'];
        $oauthAccount->token_expires_at = new Carbon(sprintf('+%d seconds', $responseBody['token']));
        $oauthAccount->refresh_token = $responseBody['refresh_token'];
        $oauthAccount->save();
    }
}

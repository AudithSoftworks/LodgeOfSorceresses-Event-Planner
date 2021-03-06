<?php

namespace App\Services;

use App\Models\UserOAuth;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

class IpsApi extends AbstractApi
{
    public const CALENDAR_TRAINING = 11;

    public const CALENDAR_MIDGAME = 5;

    public const MEMBER_GROUP_SOULSHRIVEN = 3;

    public const MEMBER_GROUP_MEMBERS = 30;

    public const MEMBER_GROUP_MAGISTER_TEMPLI = 6;

    public const MEMBER_GROUP_IPSISSIMUS = 4;

    public const USER_ID_FOR_DANDELION = 2533;

    public function __construct()
    {
        parent::__construct('ips');
        $ipsUrl = trim(config('services.ips.url'), '/');
        $this->apiUrl = $ipsUrl . '/api/';
    }

    protected function getApiClient(): GuzzleClient
    {
        if (($this->apiClient ?? null) !== null) {
            return $this->apiClient;
        }

        $apiKey = config('services.ips.api_key');

        return $this->apiClient = $this->createHttpClient([
            'auth' => [$apiKey, '']
        ]);
    }

    protected function getOauthClient(): GuzzleClient
    {
        if (($this->oauthClient ?? null) !== null) {
            return $this->oauthClient;
        }

        $token = $this->getToken();

        return $this->oauthClient = $this->createHttpClient([
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    }

    /**
     * @param \App\Models\UserOAuth $oauthAccount
     *
     * @throws \Exception
     */
    protected function refreshToken(UserOAuth $oauthAccount): void
    {
        $clientId = config('services.ips.client_id');
        $clientSecret = config('services.ips.client_secret');
        $httpClient = $this->createHttpClient();

        $response = $httpClient->post('/oauth/token/', [
            RequestOptions::FORM_PARAMS => [
                'grant_type' => 'refresh_token',
                'response_type' => 'token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $oauthAccount->refresh_token,
            ],
        ]);
        $responseBody = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $oauthAccount->token = $responseBody['access_token'];
        $oauthAccount->token_expires_at = new Carbon(sprintf('+%d seconds', $responseBody['expires_in']));
        $oauthAccount->save();
    }

    /*------------------------------------
     | /calendar/events
     *-----------------------------------*/

    /**
     * @return array
     * @throws \Exception
     */
    public function getCalendarEvents(): ?array
    {
        $events = [];
        $page = 1;
        while ($response = $this->getApiClient()->get('calendar/events', [RequestOptions::QUERY => ['sortBy' => 'date', 'sortDir' => 'desc', 'page' => $page, 'perPage' => 100]])) {
            $responseDecoded = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            foreach ($responseDecoded['results'] as $event) {
                $events[(new Carbon($event['start']))->getTimestamp()] = $event;
            }

            if ($responseDecoded['totalPages'] > $page) {
                $page++;
            } else {
                break;
            }
        }

        return $events;
    }

    /**
     * @param string         $title
     * @param string         $description
     * @param \Carbon\Carbon $start
     * @param bool           $rsvp
     * @param int            $rsvpLimit
     *
     * @throws \JsonException
     * @return null|array
     */
    public function createCalendarEvent(string $title, string $description, Carbon $start, bool $rsvp = true, int $rsvpLimit = 99): ?array
    {
        $response = $this->getApiClient()->post('calendar/events', [
            RequestOptions::QUERY => [
                'calendar' => self::CALENDAR_MIDGAME,
                'title' => $title,
                'description' => $description,
                'start' => $start->toIso8601String(),
                'end' => $start->addRealMinutes(150)->toIso8601String(),
                'author' => self::USER_ID_FOR_DANDELION,
                'rsvp' => $rsvp,
                'rsvpLimit' => $rsvpLimit,
                'hidden' => app()->environment('production') ? -1 : 0,
            ]
        ]);
        if ($response->getStatusCode() === Response::HTTP_CREATED) {
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }

        return null;
    }

    /*------------------------------------
     | /core/members
     *-----------------------------------*/

    /**
     * @param int $remoteUserId
     *
     * @return null|array
     */
    public function getUser(int $remoteUserId): ?array
    {
        return $this->executeCallback(function (int $remoteUserId) {
            $response = $this->getApiClient()->get('core/members/' . $remoteUserId);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $remoteUserId);
    }

    public function editUser(int $remoteUserId, array $params): ?array
    {
        return $this->executeCallback(function (int $remoteUserId, array $params) {
            $response = $this->getApiClient()->post('core/members/' . $remoteUserId, [RequestOptions::QUERY => $params]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $remoteUserId, $params);
    }

    public function deleteUser(int $remoteUserId): ?bool
    {
        return $this->executeCallback(function (int $remoteUserId) {
            $this->getApiClient()->delete('core/members/' . $remoteUserId);

            return true;
        }, $remoteUserId);
    }

    /*------------------------------------
     | /forum/topics
     *-----------------------------------*/

    public function createTopic(int $forum, string $title, string $post): ?array
    {
        return $this->executeCallback(function (int $forum, string $title, string $post) {
            $response = $this->getApiClient()->post('forums/topics/', [
                RequestOptions::QUERY => [
                    'forum' => $forum,
                    'title' => $title,
                    'post' => $post,
                    'author' => self::USER_ID_FOR_DANDELION,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $forum, $title, $post);
    }

    public function getTopic(int $topic): ?array
    {
        return $this->executeCallback(function (int $topic) {
            $response = $this->getApiClient()->get('forums/topics/' . $topic);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $topic);
    }

    /*------------------------------------
     | /forum/posts
     *-----------------------------------*/

    public function getPost(int $post): ?array
    {
        return $this->executeCallback(function (int $post) {
            $response = $this->getApiClient()->get('forums/posts/' . $post);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $post);
    }

    /*------------------------------------
     | /gallery/images
     *-----------------------------------*/

    public function getGalleryImage(int $image): ?array
    {
        return $this->executeCallback(function (int $image) {
            $response = $this->getApiClient()->get('gallery/images/' . $image);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $image);
    }

    public function postGalleryImage(int $album, int $author, string $caption, string $filename, string $image, CarbonInterface $date): ?array
    {
        return $this->executeCallback(function (int $album, int $author, string $caption, string $filename, string $image, CarbonInterface $date) {
            $response = $this->getApiClient()->post('gallery/images/', [
                RequestOptions::FORM_PARAMS => [
                    'album' => $album,
                    'author' => $author,
                    'caption' => $filename,
                    'filename' => $filename,
                    'image' => $image,
                    'description' => $caption,
                    'date' => $date->format('r'),
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $album, $author, $caption, $filename, $image, $date);
    }

    /*------------------------------------
     | /cms/records
     *-----------------------------------*/

    public function getCmsRecords(int $database): ?array
    {
        return $this->executeCallback(function (int $database) {
            $response = $this->getApiClient()->get('cms/records/' . $database);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $database);
    }

    public function getCmsRecord(int $database, int $record): ?array
    {
        return $this->executeCallback(function (int $database, int $record) {
            $response = $this->getApiClient()->get(sprintf('cms/records/%d/%d', $database, $record));

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }, $database, $record);
    }
}

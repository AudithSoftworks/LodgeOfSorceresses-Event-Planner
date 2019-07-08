<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IpsApi
{
    public const CALENDAR_TRAINING = 11;

    public const CALENDAR_MIDGAME = 5;

    public const MEMBER_GROUPS_SOULSHRIVEN = 3;

    public const MEMBER_GROUPS_INITIATE = 28;

    public const MEMBER_GROUPS_NEOPHYTE = 8;

    public const MEMBER_GROUPS_PRACTICUS = 19;

    public const MEMBER_GROUPS_ADEPTUS_MINOR = 20;

    public const MEMBER_GROUPS_ADEPTUS_MAJOR = 21;

    public const MEMBER_GROUPS_DOMINUS_LIMINIS = 22;

    public const MEMBER_GROUPS_RECTOR = 25;

    public const MEMBER_GROUPS_MAGISTER_TEMPLI = 6;

    public const MEMBER_GROUPS_IPSISSIMUS = 4;

    public const USER_ID_FOR_DANDELION = 2533;

    /**
     * @var \GuzzleHttp\Client
     */
    private $apiClient;

    /**
     * @var \GuzzleHttp\Client
     */
    private $oauthClient;

    /**
     * @var string
     */
    private $ipsUrl;

    public function __construct(Request $request)
    {
        $this->ipsUrl = trim(config('services.ips.url'), '/') . '/api';
        $this->apiClient = new Client([
            'auth' => [config('services.ips.api_key'), '']
        ]);
        if ($request->hasSession()) {
            $accessToken = $request->session()->get('token');
            $this->oauthClient = new Client([
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCalendarEvents(): array
    {
        $events = [];
        $page = 1;
        while ($response = $this->apiClient->get($this->ipsUrl . '/calendar/events', [RequestOptions::QUERY => ['sortBy' => 'date', 'sortDir' => 'desc', 'page' => $page, 'perPage' => 100]])) {
            $responseDecoded = json_decode($response->getBody()->getContents(), true);
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

    public function createCalendarEvent(string $title, string $description, Carbon $start, bool $rsvp = true, int $rsvpLimit = 99): ?array
    {
        $response = $this->apiClient->post($this->ipsUrl . '/calendar/events', [
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
            return json_decode($response->getBody()->getContents(), true);
        }

        return null;
    }

    /**
     * @param int $remoteUserId
     *
     * @return array
     */
    public function getUser(int $remoteUserId): array
    {
        $response = $this->apiClient->get($this->ipsUrl . '/core/members/' . $remoteUserId);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function editUser(int $userId, array $params): array
    {
        $response = $this->apiClient->post($this->ipsUrl . '/core/members/' . $userId, ['query' => $params]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createTopic(int $forum, string $title, string $post): array
    {
        $response = $this->apiClient->post($this->ipsUrl . '/forums/topics/', ['query' => [
            'forum' => $forum,
            'title' => $title,
            'post' => $post,
            'author' => self::USER_ID_FOR_DANDELION,
        ]]);

        return json_decode($response->getBody()->getContents(), true);
    }
}

<?php namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class IpsApi
{
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

        while ($response = $this->apiClient->get($this->ipsUrl . '/calendar/events', ['query' => ['sortBy' => 'date', 'sortDir' => 'desc', 'page' => $page, 'perPage' => 100]])) {
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
}

<?php namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class IpsApi
{
    const TIME_INTERVAL_THIS_WEEK = 1;

    const TIME_INTERVAL_LAST_WEEK = 2;

    const TIME_INTERVAL_NEXT_WEEK = 4;

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
     */
    public function getCalendarEvents()
    {
        $events = [];

        $response = $this->apiClient->get(
            $this->ipsUrl . '/calendar/events',
            ['query' => ['sortBy' => 'date', 'sortDir' => 'desc', 'page' => 1, 'perPage' => 50]]
        );
        $responseDecoded = json_decode($response->getBody()->getContents(), true);
        foreach ($responseDecoded['results'] as $event) {
            $events[(new Carbon($event['start']))->getTimestamp()] = $event;
        }
        ksort($events);

        return $events;
    }
}

<?php namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class IpsApi
{
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
}

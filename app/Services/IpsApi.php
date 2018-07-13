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
    private $client;

    /**
     * @var string
     */
    private $ipsUrl;

    /**
     * @var string
     */
    private $accessToken;

    public function __construct(Request $request)
    {
        $this->ipsUrl = trim(config('services.ips.url'), '/') . '/api';
        $this->accessToken = $request->session()->get('token');
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ]);
    }

    /**
     * @param int $timeInterval
     *
     * @return array
     */
    public function getCalendarEvents($timeInterval = self::TIME_INTERVAL_THIS_WEEK)
    {
        $timeIntervalStart = new Carbon('last Monday');
        if ($timeInterval & self::TIME_INTERVAL_LAST_WEEK) {
            $timeIntervalStart = $timeIntervalStart->subWeek();
        }

        $timeIntervalEnd = new Carbon('next Monday');
        if ($timeInterval & self::TIME_INTERVAL_NEXT_WEEK) {
            $timeIntervalEnd = $timeIntervalEnd->addWeek();
        }

        $page = 1;
        $events = [];
        while ($response = $this->client->get($this->ipsUrl . '/calendar/events', ['query' => ['sortBy' => 'date', 'sortDir' => 'desc', 'page' => $page]])) {
            $responseDecoded = json_decode($response->getBody()->getContents(), true);
            foreach ($responseDecoded['results'] as $event) {
                $eventTimestamp = new Carbon($event['start']);
                if ($eventTimestamp->between($timeIntervalStart, $timeIntervalEnd)) {
                    $events[$eventTimestamp->getTimestamp()] = $event;
                }
            }

            if ($responseDecoded['totalPages'] > $page) {
                $page++;
            } else {
                break;
            }
        }
        ksort($events);

        return $events;
    }
}

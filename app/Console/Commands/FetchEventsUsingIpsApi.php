<?php

namespace App\Console\Commands;

use App\Models\Calendar;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchEventsUsingIpsApi extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ips:events {action=sync : Action to perform. Possible values are: reset|sync|update}';

    /**
     * @var string
     */
    protected $description = 'Fetches events from IPS and Syncs them to Events repo';

    /**
     * @var array
     */
    protected $actionOptions = [
        'reset',
        'sync',
        'update',
    ];

    /**
     * @var \App\Services\IpsApi $api
     */
    protected $ipsApi;

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->ipsApi = app('ips.api');
    }

    /**
     * @return mixed
     */
    public function handle(): bool
    {
        $arguments = $this->arguments();
        if (!in_array($action = $arguments['action'], $this->actionOptions)) {
            $this->error('Invalid action: "' . $action . '"!');

            return false;
        }

        $events = $this->ipsApi->getCalendarEvents();

        foreach ($events as $event) {
            /** @var Calendar $calendarObj */
            $calendarObj = Calendar::updateOrCreate([
                'id' => $event['calendar']['id']
            ], [
                'name' => $event['calendar']['name'],
                'url' => $event['calendar']['url'],
            ]);

            $startDateTime = new Carbon($event['start']);
            $endDateTime = new Carbon($event['end']);

            Event::updateOrCreate([
                'id' => $event['id']
            ], [
                'title' => $event['title'],
                'description' => $event['description'],
                'url' => $event['url'],
                'calendar_id' => $calendarObj->id,
                'start_time' => is_null($startDateTime) ? null : $startDateTime->toDateTimeString(),
                'end_time' => is_null($endDateTime) ? null : $endDateTime->toDateTimeString(),
                'recurrence' => $event['recurrence'],
                'rsvp' => $event['rsvp'],
                'rsvp_limit' => $event['rsvpLimit'],
                'locked' => $event['locked'],
                'hidden' => $event['hidden'],
                'featured' => $event['featured'],
            ]);
        }

        return true;
    }
}

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
    protected $signature = 'ips:events';

    /**
     * @var string
     */
    protected $description = 'Fetches events from IPS and Syncs them to Events repo';

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
        $events = $this->ipsApi->getCalendarEvents();
        $this->deleteObsoleteEvents($events);
        $this->syncEvents($events);
        $this->info('Calendar events successfully synced!');

        return true;
    }

    /**
     * @param array $events
     *
     * @return int
     */
    private function deleteObsoleteEvents(array $events): int
    {
        $idsOfActiveEvents = array_column($events, 'id');
        $eventsToDelete = Event::whereNotIn('id', $idsOfActiveEvents)->get(['id'])->toArray();
        $idsOfEventsToDelete = array_column($eventsToDelete, 'id');

        return Event::destroy($idsOfEventsToDelete);
    }

    /**
     * @param array $events
     */
    private function syncEvents(array $events): void
    {
        foreach ($events as $event) {
            /** @var Calendar $calendarObj */
            $calendarObj = Calendar::updateOrCreate([
                'id' => $event['calendar']['id']
            ], [
                'name' => $event['calendar']['name'],
                'url' => $event['calendar']['url'],
            ]);

            $startDateTime = $event['start'] === null ? null : (new Carbon($event['start']))->toDateTimeString();
            $endDateTime = $event['end'] === null ? null : (new Carbon($event['end']))->toDateTimeString();

            Event::updateOrCreate([
                'id' => $event['id']
            ], [
                'title' => $event['title'],
                'description' => $event['description'],
                'url' => $event['url'],
                'calendar_id' => $calendarObj->id,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'recurrence' => $event['recurrence'],
                'rsvp' => $event['rsvp'],
                'rsvp_limit' => $event['rsvpLimit'],
                'locked' => $event['locked'],
                'hidden' => $event['hidden'],
                'featured' => $event['featured'],
            ]);
        }
    }
}

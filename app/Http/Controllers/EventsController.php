<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EventsController extends Controller
{
    private const IPS_EVENT_RECURRENCE_FREQ_DAILY = 'DAILY';

    private const IPS_EVENT_RECURRENCE_FREQ_WEEKLY = 'WEEKLY';

    private const IPS_EVENT_RECURRENCE_FREQ_MONTHLY = 'MONTHLY';

    private const IPS_EVENT_RECURRENCE_FREQ_YEARLY = 'YEARLY';

    /**
     * @var \Carbon\Carbon
     */
    public $timeIntervalStart;

    /**
     * @var \Carbon\Carbon
     */
    public $timeIntervalEnd;

    public function __construct()
    {
        $this->timeIntervalStart = new Carbon('this week Monday');
        $this->timeIntervalEnd = new Carbon('next week Monday');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('user', User::class);

        $events = Event::where(function (Builder $query) {
            $query
                ->whereBetween('start_time', [$this->timeIntervalStart->toDateTimeString(), $this->timeIntervalEnd->toDateTimeString()])
                ->where('hidden', '=', '0')
                ->orWhereNotNull('recurrence');
        })->orderBy('start_time', 'asc')->get();

        $recurringEvents = [];
        $nonRecurringEvents = [];

        foreach ($events as $event) {
            if (empty($event->recurrence)) {
                $nonRecurringEvents[] = $event;
            } elseif (!empty($parsedEvent = $this->parseRecurringEvent($event))) {
                $recurringEvents[] = $parsedEvent;
            }
        }

        $recurringEvents = $this->populateRecurringEventsForRender($recurringEvents);

        return response()->json([
            'non-recurring-events' => $nonRecurringEvents,
            'recurring-events' => $recurringEvents,
        ]);
    }

    /**
     * @param \App\Models\Event $event
     *
     * @return null|array
     * @throws \Exception
     */
    private function parseRecurringEvent(Event $event): ?array
    {
        $parsedEvent = $event->toArray();
        $recurrenceParsed = [];
        $recurrenceExploded = explode(';', $event->recurrence);
        foreach ($recurrenceExploded as $recurrenceFragments) {
            [$key, $value] = explode('=', $recurrenceFragments);
            $recurrenceParsed = array_merge($recurrenceParsed, array_combine([$key], [$value]));
        }
        if (isset($recurrenceParsed['UNTIL'])) {
            $recurrenceParsed['UNTIL'] = new Carbon($recurrenceParsed['UNTIL']);
            if (!$recurrenceParsed['UNTIL']->isFuture()) {
                return null;
            }
        }

        $recurrenceParsed['INTERVAL'] = (int)$recurrenceParsed['INTERVAL'];
        isset($recurrenceParsed['COUNT']) && settype($recurrenceParsed['COUNT'], 'int');

        if (!$this->checkIfRecurringEventHappensThisWeek($event, $recurrenceParsed)) {
            return null;
        }
        $parsedEvent['recurrenceParsed'] = $recurrenceParsed;

        return $parsedEvent;
    }

    /**
     * @param \App\Models\Event $event
     * @param array             $recurrenceParsed
     *
     * @return bool
     * @throws \Exception
     */
    private function checkIfRecurringEventHappensThisWeek(Event $event, array &$recurrenceParsed): bool
    {
        $now = new Carbon();
        $eventTime = new Carbon($event->start_time);
        $decreaseRecurrenceCounterBy = 1;
        isset($recurrenceParsed['BYDAY']) && $decreaseRecurrenceCounterBy = count(explode(',', $recurrenceParsed['BYDAY']));

        $recurrenceParsed['LAST_EVENT_TIME'] = clone $eventTime;
        while (!$eventTime->isSameAs('W', $now)) {
            if ($eventTime->isFuture()) {
                return false;
            }
            switch ($recurrenceParsed['FREQ']) {
                case self::IPS_EVENT_RECURRENCE_FREQ_DAILY:
                    $eventTime->addDays($recurrenceParsed['INTERVAL']);
                    break;
                case self::IPS_EVENT_RECURRENCE_FREQ_WEEKLY:
                    $eventTime->addWeeks($recurrenceParsed['INTERVAL']);
                    break;
                case self::IPS_EVENT_RECURRENCE_FREQ_MONTHLY:
                    $eventTime->addMonths($recurrenceParsed['INTERVAL']);
                    break;
                case self::IPS_EVENT_RECURRENCE_FREQ_YEARLY:
                    $eventTime->addYears($recurrenceParsed['INTERVAL']);
                    break;
            }
            if ($eventTime->greaterThan($this->timeIntervalStart)) {
                // We entered current week. 'Continue' to reset iteration in order to exit it.
                continue;
            }
            if (isset($recurrenceParsed['COUNT'])) {
                if ($recurrenceParsed['COUNT'] > 0) {
                    // Update 'COUNT'
                    $recurrenceParsed['COUNT'] -= $decreaseRecurrenceCounterBy;
                    $recurrenceParsed['LAST_EVENT_TIME'] = clone $eventTime;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    private function populateRecurringEventsForRender(array $events)
    {
        foreach ($events as &$event) {
            $recurrenceStartTimes = [];

            /** @var Carbon $lastEventTime */
            $lastEventTime = $event['recurrenceParsed']['LAST_EVENT_TIME'];
            if ($lastEventTime->greaterThan($this->timeIntervalStart)) {
                $recurrenceStartTimes[] = $lastEventTime->toDateTimeString();
            }

            switch ($event['recurrenceParsed']['FREQ']) {
                case self::IPS_EVENT_RECURRENCE_FREQ_DAILY:
                    while (!$lastEventTime->greaterThan($this->timeIntervalEnd)) {
                        $lastEventTime->addDays($event['recurrenceParsed']['INTERVAL']);
                        if (isset($event['recurrenceParsed']['COUNT'])) {
                            if ($event['recurrenceParsed']['COUNT'] > 0) {
                                $event['recurrenceParsed']['COUNT']--;
                            } else {
                                break;
                            }
                        }
                        $recurrenceStartTimes[] = $lastEventTime->toDateTimeString();
                    }
                    break;
                case self::IPS_EVENT_RECURRENCE_FREQ_WEEKLY:
                    if (isset($event['recurrenceParsed']['BYDAY'])) {
                        $weekDayMap = [
                            'MO' => 'Monday',
                            'TU' => 'Tuesday',
                            'WE' => 'Wednesday',
                            'TH' => 'Thursday',
                            'FR' => 'Friday',
                            'SA' => 'Saturday',
                            'SU' => 'Sunday'
                        ];
                        $recurringDaysOfWeek = explode(',', $event['recurrenceParsed']['BYDAY']);
                        foreach ($recurringDaysOfWeek as $day) {
                            $eventTime = new Carbon('this week ' . $weekDayMap[$day]);
                            $eventTime->setTime($lastEventTime->hour, $lastEventTime->minute, $lastEventTime->second);
                            if (isset($event['recurrenceParsed']['COUNT'])) {
                                if ($event['recurrenceParsed']['COUNT'] > 0) {
                                    $event['recurrenceParsed']['COUNT']--;
                                } else {
                                    break;
                                }
                            }
                            $recurrenceStartTimes[] = $eventTime->toDateTimeString();
                        }
                    } else {
                        if (!$lastEventTime->greaterThan($this->timeIntervalStart)) {
                            $lastEventTime->addWeek();
                            if (isset($event['recurrenceParsed']['COUNT'])) {
                                if ($event['recurrenceParsed']['COUNT'] > 0) {
                                    $event['recurrenceParsed']['COUNT']--;
                                } else {
                                    break;
                                }
                            }
                            $recurrenceStartTimes[] = $lastEventTime->toDateTimeString();
                        }
                    }
                    break;
                case self::IPS_EVENT_RECURRENCE_FREQ_MONTHLY:
                    if (!$lastEventTime->greaterThan($this->timeIntervalStart)) {
                        $lastEventTime->addMonth();
                        if (isset($event['recurrenceParsed']['COUNT'])) {
                            if ($event['recurrenceParsed']['COUNT'] > 0) {
                                $event['recurrenceParsed']['COUNT']--;
                            } else {
                                break;
                            }
                        }
                        $recurrenceStartTimes[] = clone $lastEventTime;
                    }
                    break;
                case self::IPS_EVENT_RECURRENCE_FREQ_YEARLY:
                    if (!$lastEventTime->greaterThan($this->timeIntervalStart)) {
                        $lastEventTime->addYear();
                        if (isset($event['recurrenceParsed']['COUNT'])) {
                            if ($event['recurrenceParsed']['COUNT'] > 0) {
                                $event['recurrenceParsed']['COUNT']--;
                            } else {
                                break;
                            }
                        }
                        $recurrenceStartTimes[] = $lastEventTime->toDateTimeString();
                    }
                    break;
            }

            $event['recurrenceParsed'] = $recurrenceStartTimes;
        }

        return $events;
    }
}

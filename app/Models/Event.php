<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $url
 * @property int|null $calendar_id
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $recurrence
 * @property int $rsvp
 * @property int|null $rsvp_limit
 * @property int $locked
 * @property int $hidden
 * @property int $featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Calendar|null $calendar
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCalendarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRecurrence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRsvp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRsvpLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUrl($value)
 */
class Event extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'events';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id', 'id');
    }
}

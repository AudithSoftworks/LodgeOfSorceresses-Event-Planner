<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar query()
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereUrl($value)
 */
class Calendar extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'calendars';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}

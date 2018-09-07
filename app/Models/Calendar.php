<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 *
 * @property int    $id
 * @property string $name
 * @property string $url
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereUrl($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Event[] $events
 */
class Calendar extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = "calendars";

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}

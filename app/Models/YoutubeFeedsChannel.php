<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\YoutubeFeedsChannel
 *
 * @property int $id
 * @property string $title
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\YoutubeFeedsVideo[] $videos
 * @method static \Illuminate\Database\Eloquent\Builder|$this newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|$this newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|$this query()
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereUrl($value)
 * @mixin \Eloquent
 */
class YoutubeFeedsChannel extends Model
{
    public $incrementing = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function videos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(YoutubeFeedsVideo::class, 'channel_id', 'id');
    }
}

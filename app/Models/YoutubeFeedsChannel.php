<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\YoutubeFeedsChannel
 *
 * @property string $id
 * @property string $title
 * @property string $url
 * @property string $thumbnail
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EloquentCollection|YoutubeFeedsVideo[] $videos
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereTitle($value)
 * @method static EloquentBuilder|$this whereUrl($value)
 * @method static EloquentBuilder|$this whereThumbnail($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 * @property-read int|null $videos_count
 */
class YoutubeFeedsChannel extends Model
{
    public $incrementing = false;

    public function videos(): HasMany
    {
        return $this->hasMany(YoutubeFeedsVideo::class, 'channel_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $channel_id
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $thumbnail
 * @property string|null $discord_message_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read YoutubeFeedsChannel $channel
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereChannelId($value)
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereDescription($value)
 * @method static EloquentBuilder|$this whereDiscordMessageId($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereThumbnail($value)
 * @method static EloquentBuilder|$this whereTitle($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 * @method static EloquentBuilder|$this whereUrl($value)
 */
class YoutubeFeedsVideo extends Model
{
    public $incrementing = false;

    public function channel(): BelongsTo
    {
        return $this->belongsTo(YoutubeFeedsChannel::class, 'channel_id', 'id');
    }
}

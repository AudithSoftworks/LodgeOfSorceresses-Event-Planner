<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $channel_id
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $thumbnail
 * @property string|null $discord_message_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\YoutubeFeedsChannel $channel
 * @method static \Illuminate\Database\Eloquent\Builder|$this newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|$this newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|$this query()
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereDiscordMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|$this whereUrl($value)
 * @mixin \Eloquent
 */
class YoutubeFeedsVideo extends Model
{
    public $incrementing = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(YoutubeFeedsChannel::class, 'channel_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $text
 * @property string $text_for_forums
 * @property string $text_for_planner
 * @property string $discord_message_id
 * @property null|string $gallery_image_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EloquentCollection|User[] $attendees
 * @property-read int|null $attendees_count
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereDiscordMessageId($value)
 * @method static EloquentBuilder|$this whereGalleryImageIds($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereText($value)
 * @method static EloquentBuilder|$this whereTextForForums($value)
 * @method static EloquentBuilder|$this whereTextForPlanner($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 */
class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    public function attendees(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'attendances_users')
            ->as('attendees')
            ->withTimestamps(['is_author']);
    }
}

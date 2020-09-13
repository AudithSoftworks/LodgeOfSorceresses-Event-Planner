<?php

namespace App\Models;

use App\Events\Team\TeamDeleted;
use App\Events\Team\TeamUpdated;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property int $tier
 * @property string $discord_role_id
 * @property string $discord_lobby_channel_id
 * @property string $discord_rant_channel_id
 * @property int $led_by
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $createdBy
 * @property-read User $ledBy
 * @property-read EloquentCollection|Character[] $members
 * @property-read int|null $members_count
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereDiscordId($value)
 * @method static EloquentBuilder|$this whereLedBy($value)
 * @method static EloquentBuilder|$this whereCreatedBy($value)
 * @method static EloquentBuilder|$this whereIcon($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereName($value)
 * @method static EloquentBuilder|$this whereOwner($value)
 * @method static EloquentBuilder|$this whereTier($value)
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 */
class Team extends Model
{
    use HasFactory;

    protected $table = 'teams';

    protected $dispatchesEvents = [
        'deleted' => TeamDeleted::class,
        'updated' => TeamUpdated::class,
    ];

    public function members(): BelongsToMany
    {
        return $this
            ->belongsToMany(Character::class, 'teams_characters')
            ->as('teamMembership')
            ->withTimestamps()
            ->withPivot(['status', 'accepted_terms']);
    }

    public function ledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'led_by', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}

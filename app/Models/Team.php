<?php

namespace App\Models;

use App\Events\Team\TeamCreated;
use App\Events\Team\TeamDeleted;
use App\Events\Team\TeamUpdated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string $tier
 * @property int $discord_id
 * @property int $led_by
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User $ledBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Character[] $members
 * @property-read int|null $members_count
 * @method static Builder|$this newModelQuery()
 * @method static Builder|$this newQuery()
 * @method static Builder|$this query()
 * @method static Builder|$this whereDiscordId($value)
 * @method static Builder|$this whereLedBy($value)
 * @method static Builder|$this whereCreatedBy($value)
 * @method static Builder|$this whereIcon($value)
 * @method static Builder|$this whereId($value)
 * @method static Builder|$this whereName($value)
 * @method static Builder|$this whereOwner($value)
 * @method static Builder|$this whereTier($value)
 * @method static Builder|$this whereCreatedAt($value)
 * @method static Builder|$this whereUpdatedAt($value)
 */
class Team extends Model
{
    protected $table = 'teams';
    /**
     * {@inheritdoc}
     */
    protected $dispatchesEvents = [
        'deleted' => TeamDeleted::class,
        'updated' => TeamUpdated::class,
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'teams_characters')->withTimestamps()->withPivot(['role', 'status', 'accepted_terms']);
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

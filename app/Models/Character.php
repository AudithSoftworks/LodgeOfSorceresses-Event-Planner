<?php

namespace App\Models;

use App\Events\Character\CharacterDeleted;
use App\Events\Character\CharacterDeleting;
use App\Events\Character\CharacterUpdated;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $class
 * @property int $role
 * @property string $sets
 * @property string|null $skills
 * @property int $approved_for_tier
 * @property int|null $last_submitted_dps_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read EloquentCollection|\App\Models\Content[] $content
 * @property-read int|null $content_count
 * @property-read EloquentCollection|\App\Models\DpsParse[] $dpsParses
 * @property-read int|null $dps_parses_count
 * @property-read \App\Models\User $owner
 * @property-read EloquentCollection|\App\Models\Team[] $teams
 * @property-read int|null $teams_count
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereApprovedForTier($value)
 * @method static EloquentBuilder|$this whereClass($value)
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereLastSubmittedDpsAmount($value)
 * @method static EloquentBuilder|$this whereName($value)
 * @method static EloquentBuilder|$this whereRole($value)
 * @method static EloquentBuilder|$this whereSets($value)
 * @method static EloquentBuilder|$this whereSkills($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 * @method static EloquentBuilder|$this whereUserId($value)
 */
class Character extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $dispatchesEvents = [
        'deleting' => CharacterDeleting::class,
        'deleted' => CharacterDeleted::class,
        'updated' => CharacterUpdated::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'name',
        'class',
        'role',
        'sets',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function content(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'characters_content', 'character_id', 'content_id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dpsParses(): HasMany
    {
        return $this->hasMany(DpsParse::class);
    }

    public function teams(): BelongsToMany
    {
        return $this
            ->belongsToMany(Team::class, 'teams_characters')
            ->as('teamMembership')
            ->withTimestamps()
            ->withPivot(['status', 'accepted_terms']);
    }
}

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
 * @property string                                         $sets
 * @property string|null                                    $skills
 * @property string|null                                    $content
 * @property int                                            $approved_for_tier
 * @property int|null                                       $last_submitted_dps_amount
 * @property \Illuminate\Support\Carbon|null                $created_at
 * @property \Illuminate\Support\Carbon|null                $updated_at
 * @property-read \App\Models\User                          $owner
 * @property-read EloquentCollection|Team[]                 $teams
 * @property-read int|null                                  $teams_count
 * @property-read EloquentCollection|\App\Models\DpsParse[] $dpsParses
 * @property EloquentCollection|\App\Models\DpsParse[]      $dps_parses_processed
 * @property EloquentCollection|\App\Models\DpsParse[]      $dps_parses_pending
 * @method EloquentBuilder|Character newModelQuery()
 * @method EloquentBuilder|Character newQuery()
 * @method static EloquentBuilder|Character query()
 * @method EloquentBuilder|Character whereApprovedForTier($value)
 * @method EloquentBuilder|Character whereClass($value)
 * @method EloquentBuilder|Character whereContent($value)
 * @method EloquentBuilder|Character whereCreatedAt($value)
 * @method EloquentBuilder|Character whereId($value)
 * @method EloquentBuilder|Character whereLastSubmittedDpsAmount($value)
 * @method EloquentBuilder|Character whereName($value)
 * @method EloquentBuilder|Character whereRole($value)
 * @method EloquentBuilder|Character whereSets($value)
 * @method EloquentBuilder|Character whereSkills($value)
 * @method EloquentBuilder|Character whereUpdatedAt($value)
 * @method EloquentBuilder|Character whereUserId($value)
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
        return $this->belongsToMany(Team::class, 'teams_characters')->withTimestamps()->withPivot(['status', 'accepted_terms']);
    }
}

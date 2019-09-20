<?php

namespace App\Models;

use App\Events\Character\CharacterDeleted;
use App\Events\Character\CharacterDeleting;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $class
 * @property int $role
 * @property string $sets
 * @property string|null $skills
 * @property string|null $content
 * @property int $approved_for_t1
 * @property int $approved_for_t2
 * @property int $approved_for_t3
 * @property int $approved_for_t4
 * @property int|null $last_submitted_dps_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DpsParse[] $dpsParses
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\DpsParse[] $dps_parses_processed
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\DpsParse[] $dps_parses_pending
 * @method static EloquentBuilder|Character newModelQuery()
 * @method static EloquentBuilder|Character newQuery()
 * @method static EloquentBuilder|Character query()
 * @method static EloquentBuilder|Character whereApprovedForT1($value)
 * @method static EloquentBuilder|Character whereApprovedForT2($value)
 * @method static EloquentBuilder|Character whereApprovedForT3($value)
 * @method static EloquentBuilder|Character whereApprovedForT4($value)
 * @method static EloquentBuilder|Character whereClass($value)
 * @method static EloquentBuilder|Character whereContent($value)
 * @method static EloquentBuilder|Character whereCreatedAt($value)
 * @method static EloquentBuilder|Character whereId($value)
 * @method static EloquentBuilder|Character whereLastSubmittedDpsAmount($value)
 * @method static EloquentBuilder|Character whereName($value)
 * @method static EloquentBuilder|Character whereRole($value)
 * @method static EloquentBuilder|Character whereSets($value)
 * @method static EloquentBuilder|Character whereSkills($value)
 * @method static EloquentBuilder|Character whereUpdatedAt($value)
 * @method static EloquentBuilder|Character whereUserId($value)
 */
class Character extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $dispatchesEvents = [
        'deleting' => CharacterDeleting::class,
        'deleted' => CharacterDeleted::class,
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
    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function content(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'characters_content', 'character_id', 'content_id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dpsParses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsParse::class);
    }
}

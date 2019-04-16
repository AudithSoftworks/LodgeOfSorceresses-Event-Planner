<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $name
 * @property int                             $class
 * @property int                             $role
 * @property string                          $sets
 * @property int                             $approved_for_midgame
 * @property int                             $approved_for_endgame_t0
 * @property int                             $approved_for_endgame_t1
 * @property int                             $approved_for_endgame_t2
 * @property int|null                        $last_submitted_dps_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User           $owner
 * @method static EloquentBuilder|Character newModelQuery()
 * @method static EloquentBuilder|Character newQuery()
 * @method static EloquentBuilder|Character query()
 * @method static EloquentBuilder|Character whereApprovedForEndgameT0($value)
 * @method static EloquentBuilder|Character whereApprovedForEndgameT1($value)
 * @method static EloquentBuilder|Character whereApprovedForEndgameT2($value)
 * @method static EloquentBuilder|Character whereApprovedForMidgame($value)
 * @method static EloquentBuilder|Character whereClass($value)
 * @method static EloquentBuilder|Character whereCreatedAt($value)
 * @method static EloquentBuilder|Character whereLastSubmittedDpsAmount($value)
 * @method static EloquentBuilder|Character whereId($value)
 * @method static EloquentBuilder|Character whereName($value)
 * @method static EloquentBuilder|Character whereRole($value)
 * @method static EloquentBuilder|Character whereSets($value)
 * @method static EloquentBuilder|Character whereUpdatedAt($value)
 * @method static EloquentBuilder|Character whereUserId($value)
 */
class Character extends Model
{
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
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Character
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $name
 * @property int                             $class
 * @property int                             $role
 * @property string                          $sets
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static EloquentBuilder|Character newModelQuery()
 * @method static EloquentBuilder|Character newQuery()
 * @method static EloquentBuilder|Character query()
 * @method static EloquentBuilder|Character whereClass($value)
 * @method static EloquentBuilder|Character whereCreatedAt($value)
 * @method static EloquentBuilder|Character whereId($value)
 * @method static EloquentBuilder|Character whereName($value)
 * @method static EloquentBuilder|Character whereRole($value)
 * @method static EloquentBuilder|Character whereSets($value)
 * @method static EloquentBuilder|Character whereUpdatedAt($value)
 * @method static EloquentBuilder|Character whereUserId($value)
 * @property int                             $approved_for_midgame
 * @property int                             $approved_for_endgame_t0
 * @property int                             $approved_for_endgame_t1
 * @property int                             $approved_for_endgame_t2
 * @method static EloquentBuilder|Character whereApprovedForEndgameT0($value)
 * @method static EloquentBuilder|Character whereApprovedForEndgameT1($value)
 * @method static EloquentBuilder|Character whereApprovedForEndgameT2($value)
 * @method static EloquentBuilder|Character whereApprovedForMidgame($value)
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
}

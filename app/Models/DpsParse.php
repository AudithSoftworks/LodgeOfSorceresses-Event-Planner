<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                             $id
 * @property int                             $user_id
 * @property int                             $character_id
 * @property string                          $sets
 * @property int                             $dps_amount
 * @property string                          $parse_file_hash
 * @property string|null                     $superstar_file_hash
 * @property string|null                     $discord_notification_message_ids
 * @property int                             $processed_by
 * @property int                             $approved_for_midgame
 * @property int                             $approved_for_endgame_t0
 * @property int                             $approved_for_endgame_t1
 * @property int                             $approved_for_endgame_t2
 * @property string|null                     $reason_for_disapproval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User           $approvedBy
 * @property-read \App\Models\Character      $character
 * @property-read \App\Models\User           $owner
 * @property-read \App\Models\File           $parseScreenshot
 * @property-read \App\Models\File           $superstarScreenshot
 * @method static bool|null forceDelete()
 * @method static EloquentBuilder|DpsParse newModelQuery()
 * @method static EloquentBuilder|DpsParse newQuery()
 * @method static QueryBuilder|DpsParse onlyTrashed()
 * @method static EloquentBuilder|DpsParse query()
 * @method static bool|null restore()
 * @method static EloquentBuilder|DpsParse whereApprovedForEndgameT0($value)
 * @method static EloquentBuilder|DpsParse whereApprovedForEndgameT1($value)
 * @method static EloquentBuilder|DpsParse whereApprovedForEndgameT2($value)
 * @method static EloquentBuilder|DpsParse whereApprovedForMidgame($value)
 * @method static EloquentBuilder|DpsParse whereCharacterId($value)
 * @method static EloquentBuilder|DpsParse whereCreatedAt($value)
 * @method static EloquentBuilder|DpsParse whereDeletedAt($value)
 * @method static EloquentBuilder|DpsParse whereDiscordNotificationMessageIds($value)
 * @method static EloquentBuilder|DpsParse whereDpsAmount($value)
 * @method static EloquentBuilder|DpsParse whereId($value)
 * @method static EloquentBuilder|DpsParse whereParseFileHash($value)
 * @method static EloquentBuilder|DpsParse whereProcessedBy($value)
 * @method static EloquentBuilder|DpsParse whereReasonForDisapproval($value)
 * @method static EloquentBuilder|DpsParse whereSets($value)
 * @method static EloquentBuilder|DpsParse whereSuperstarFileHash($value)
 * @method static EloquentBuilder|DpsParse whereUpdatedAt($value)
 * @method static EloquentBuilder|DpsParse whereUserId($value)
 * @method static QueryBuilder|DpsParse withTrashed()
 * @method static QueryBuilder|DpsParse withoutTrashed()
 */
class DpsParse extends Model
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $table = 'dps_parses';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parseScreenshot(): HasOne
    {
        return $this->hasOne(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function superstarScreenshot(): HasOne
    {
        return $this->hasOne(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

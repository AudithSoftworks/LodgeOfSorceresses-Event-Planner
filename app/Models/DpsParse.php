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
 * @property string|null                     $reason_for_disapproval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User           $approvedBy
 * @property-read \App\Models\Character      $character
 * @property-read \App\Models\User           $owner
 * @property-read \App\Models\File           $parseScreenshot
 * @property-read \App\Models\File           $superstarScreenshot
 * @method bool|null forceDelete()
 * @method EloquentBuilder|DpsParse newModelQuery()
 * @method EloquentBuilder|DpsParse newQuery()
 * @method QueryBuilder|DpsParse onlyTrashed()
 * @method static EloquentBuilder|DpsParse query()
 * @method bool|null restore()
 * @method EloquentBuilder|DpsParse whereCharacterId($value)
 * @method EloquentBuilder|DpsParse whereCreatedAt($value)
 * @method EloquentBuilder|DpsParse whereDeletedAt($value)
 * @method EloquentBuilder|DpsParse whereDiscordNotificationMessageIds($value)
 * @method EloquentBuilder|DpsParse whereDpsAmount($value)
 * @method EloquentBuilder|DpsParse whereId($value)
 * @method EloquentBuilder|DpsParse whereParseFileHash($value)
 * @method EloquentBuilder|DpsParse whereProcessedBy($value)
 * @method EloquentBuilder|DpsParse whereReasonForDisapproval($value)
 * @method EloquentBuilder|DpsParse whereSets($value)
 * @method EloquentBuilder|DpsParse whereSuperstarFileHash($value)
 * @method EloquentBuilder|DpsParse whereUpdatedAt($value)
 * @method EloquentBuilder|DpsParse whereUserId($value)
 * @method QueryBuilder|DpsParse withTrashed()
 * @method QueryBuilder|DpsParse withoutTrashed()
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

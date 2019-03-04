<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $character_id
 * @property string $sets
 * @property int $dps_amount
 * @property string $parse_file_hash
 * @property string|null $superstar_file_hash
 * @property string|null $discord_notification_message_ids
 * @property int|null $approved_by
 * @property int $approved_for_midgame
 * @property int $approved_for_endgame_t0
 * @property int $approved_for_endgame_t1
 * @property int $approved_for_endgame_t2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $approvedBy
 * @property-read \App\Models\Character $character
 * @property-read \App\Models\User $owner
 * @property-read \App\Models\File $parseScreenshot
 * @property-read \App\Models\File $superstarScreenshot
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse newQuery()
 * @method static \Illuminate\Database\Query\Builder|DpsParse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereApprovedForEndgameT0($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereApprovedForEndgameT1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereApprovedForEndgameT2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereApprovedForMidgame($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereCharacterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereDiscordNotificationMessageIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereDpsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereParseFileHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereSets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereSuperstarFileHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DpsParse whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|DpsParse withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DpsParse withoutTrashed()
 */
class DpsParse extends Model
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $table = "dps_parses";

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
    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parseScreenshot()
    {
        return $this->hasOne(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function superstarScreenshot()
    {
        return $this->hasOne(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class);
    }
}

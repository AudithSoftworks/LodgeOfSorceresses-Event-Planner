<?php

namespace App\Models;

use App\Events\DpsParse\DpsParseSubmitted;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $character_id
 * @property string $sets
 * @property int $dps_amount
 * @property string $parse_file_hash
 * @property string|null $info_file_hash
 * @property string|null $discord_notification_message_ids
 * @property int|null $processed_by
 * @property string|null $reason_for_disapproval
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $processedBy
 * @property-read Character $character
 * @property-read File $infoScreenshot
 * @property-read User $owner
 * @property-read File $parseScreenshot
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static QueryBuilder|$this onlyTrashed()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereCharacterId($value)
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereDeletedAt($value)
 * @method static EloquentBuilder|$this whereDiscordNotificationMessageIds($value)
 * @method static EloquentBuilder|$this whereDpsAmount($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereInfoFileHash($value)
 * @method static EloquentBuilder|$this whereParseFileHash($value)
 * @method static EloquentBuilder|$this whereProcessedBy($value)
 * @method static EloquentBuilder|$this whereReasonForDisapproval($value)
 * @method static EloquentBuilder|$this whereSets($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 * @method static EloquentBuilder|$this whereUserId($value)
 * @method static QueryBuilder|$this withTrashed()
 * @method static QueryBuilder|$this withoutTrashed()
 */
class DpsParse extends Model
{
    use HasFactory, SoftDeletes;

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
     * {@inheritdoc}
     */
    protected $dispatchesEvents = [
        'created' => DpsParseSubmitted::class,
    ];

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
    public function infoScreenshot(): HasOne
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

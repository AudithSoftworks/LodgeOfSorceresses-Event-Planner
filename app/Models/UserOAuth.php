<?php namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $remote_provider
 * @property string $remote_id
 * @property int $remote_primary_group
 * @property string|null $remote_secondary_groups
 * @property string|null $nickname
 * @property string|null $name
 * @property string|null $email
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $owner
 * @method static Builder|UserOAuth newModelQuery()
 * @method static Builder|UserOAuth newQuery()
 * @method static Builder|UserOAuth ofProvider($provider)
 * @method static Builder|UserOAuth query()
 * @method static Builder|UserOAuth whereAvatar($value)
 * @method static Builder|UserOAuth whereCreatedAt($value)
 * @method static Builder|UserOAuth whereEmail($value)
 * @method static Builder|UserOAuth whereId($value)
 * @method static Builder|UserOAuth whereName($value)
 * @method static Builder|UserOAuth whereNickname($value)
 * @method static Builder|UserOAuth whereRemoteId($value)
 * @method static Builder|UserOAuth whereRemotePrimaryGroup($value)
 * @method static Builder|UserOAuth whereRemoteProvider($value)
 * @method static Builder|UserOAuth whereRemoteSecondaryGroups($value)
 * @method static Builder|UserOAuth whereUpdatedAt($value)
 * @method static Builder|UserOAuth whereUserId($value)
 */
class UserOAuth extends Model
{
    protected $table = 'users_oauth';

    protected $hidden = [];

    protected $fillable = ['*'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @param Builder $query
     * @param string  $provider
     *
     * @return mixed
     */
    public function scopeOfProvider(Builder $query, $provider)
    {
        return $query->where('remote_provider', '=', $provider);
    }
}

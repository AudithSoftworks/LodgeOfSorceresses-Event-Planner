<?php namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $remote_provider
 * @property string $remote_id
 * @property int|null $remote_primary_group
 * @property string|null $remote_secondary_groups
 * @property string|null $nickname
 * @property string|null $name
 * @property string|null $email
 * @property string|null $avatar
 * @property int $verified
 * @property string $token
 * @property string $token_expires_at
 * @property string $refresh_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $owner
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this ofProvider($provider)
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereAvatar($value)
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereEmail($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereName($value)
 * @method static EloquentBuilder|$this whereNickname($value)
 * @method static EloquentBuilder|$this whereRefreshToken($value)
 * @method static EloquentBuilder|$this whereRemoteId($value)
 * @method static EloquentBuilder|$this whereRemotePrimaryGroup($value)
 * @method static EloquentBuilder|$this whereRemoteProvider($value)
 * @method static EloquentBuilder|$this whereRemoteSecondaryGroups($value)
 * @method static EloquentBuilder|$this whereToken($value)
 * @method static EloquentBuilder|$this whereTokenExpiresAt($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 * @method static EloquentBuilder|$this whereUserId($value)
 * @method static EloquentBuilder|$this whereVerified($value)
 */
class UserOAuth extends Model
{
    use HasFactory;

    protected $table = 'users_oauth';

    protected $fillable = ['*'];

    protected $hidden = ['remote_id', 'name', 'email', 'token', 'token_expires_at', 'refresh_token'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @param EloquentBuilder $query
     * @param string  $provider
     *
     * @return mixed
     */
    public function scopeOfProvider(EloquentBuilder $query, $provider)
    {
        return $query->where('remote_provider', '=', $provider);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token as PassportToken;

/**
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property string|null $avatar
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read EloquentCollection|Character[] $characters
 * @property-read int|null $characters_count
 * @property-read EloquentCollection|PassportClient[] $clients
 * @property-read int|null $clients_count
 * @property-read EloquentCollection|File[] $files
 * @property-read int|null $files_count
 * @property-read EloquentCollection|UserOAuth[] $linkedAccounts
 * @property-read int|null $linked_accounts_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read EloquentCollection|Team[] $teamsLed
 * @property-read int|null $teams_led_count
 * @property-read EloquentCollection|PassportToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read EloquentCollection|Attendance[] $attendances
 * @property-read int|null $attendances_count
 * @property array $clearanceLevel
 * @property bool $isSoulshriven
 * @property bool $isMember
 * @property bool $isAdmin
 * @property EloquentCollection|UserOAuth[] $linkedAccountsParsed
 * @method bool|null restore()
 * @method bool|null forceDelete()
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static QueryBuilder|$this onlyTrashed()
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereAvatar($value)
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereDeletedAt($value)
 * @method static EloquentBuilder|$this whereEmail($value)
 * @method static EloquentBuilder|$this whereId($value)
 * @method static EloquentBuilder|$this whereName($value)
 * @method static EloquentBuilder|$this wherePassword($value)
 * @method static EloquentBuilder|$this whereRememberToken($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 * @method static QueryBuilder|$this withTrashed()
 * @method static QueryBuilder|$this withoutTrashed()
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * {@inheritdoc}
     */
    protected $dates = ['deleted_at'];

    public function linkedAccounts(): HasMany
    {
        return $this->hasMany(UserOAuth::class, 'user_id', 'id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'files_users', 'user_id', 'file_hash')->withTimestamps()->withPivot(['id', 'qquuid', 'original_client_name', 'tag']);
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class, 'user_id', 'id');
    }

    public function teamsLed(): HasMany
    {
        return $this->hasMany(Team::class, 'owner', 'id');
    }

    public function attendances(): BelongsToMany
    {
        return $this->belongsToMany(Attendance::class, 'attendances_users')->as('attendances');
    }
}

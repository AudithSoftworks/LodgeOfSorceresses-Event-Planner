<?php namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @property int                             $id
 * @property string|null                     $name
 * @property string                          $email
 * @property string                          $avatar
 * @property string                          $password
 * @property string|null                     $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read EloquentCollection|\App\Models\Character[]                                                                $characters
 * @property-read EloquentCollection|\Laravel\Passport\Client[]                                                             $clients
 * @property-read EloquentCollection|\App\Models\File[]                                                                     $files
 * @property-read EloquentCollection|\App\Models\UserOAuth[]                                                                $linkedAccounts
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read EloquentCollection|\Laravel\Passport\Token[]                                                              $tokens
 * @property iterable                                                                                                       $linkedAccountsParsed
 * @property bool                                                                                                           isMember
 * @property bool                                                                                                           isSoulshriven
 * @method static EloquentBuilder|User newModelQuery()
 * @method static EloquentBuilder|User newQuery()
 * @method static QueryBuilder|User onlyTrashed()
 * @method static EloquentBuilder|User query()
 * @method static bool|null restore()
 * @method static EloquentBuilder|User whereAvatar($value)
 * @method static EloquentBuilder|User whereCreatedAt($value)
 * @method static EloquentBuilder|User whereDeletedAt($value)
 * @method static EloquentBuilder|User whereEmail($value)
 * @method static EloquentBuilder|User whereEmailVerifiedAt($value)
 * @method static EloquentBuilder|User whereId($value)
 * @method static EloquentBuilder|User whereName($value)
 * @method static EloquentBuilder|User wherePassword($value)
 * @method static EloquentBuilder|User whereRememberToken($value)
 * @method static EloquentBuilder|User whereUpdatedAt($value)
 * @method static QueryBuilder|User withTrashed()
 * @method static QueryBuilder|User withoutTrashed()
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function linkedAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserOAuth::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(File::class, 'files_users', 'user_id', 'file_hash')->withTimestamps()->withPivot(['id', 'qquuid', 'original_client_name', 'tag']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function characters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Character::class, 'user_id', 'id');
    }
}

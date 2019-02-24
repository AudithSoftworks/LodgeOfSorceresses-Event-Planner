<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $hash
 * @property string $disk
 * @property string $path
 * @property string $mime
 * @property int $size
 * @property string|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $uploaders
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File ofType($type = 'image')
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 */
class File extends Model
{
    protected $table = 'files';

    protected $primaryKey = 'hash';

    public $incrementing = false;

    protected $guarded = ['*'];

    protected $dates = ['deleted_at'];

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $type
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Exception
     */
    public function scopeOfType($query, $type = 'image')
    {
        if (!in_array($type, ['plain', 'image', 'audio', 'video', 'application'])) {
            throw new \Exception();
        }

        return $query->where('mime', 'like', $type . '/%');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function uploaders()
    {
        return $this->belongsToMany(User::class, 'files_users', 'file_hash', 'user_id')->withTimestamps()->withPivot(['id', 'qquuid', 'original_client_name', 'tag']);
    }
}

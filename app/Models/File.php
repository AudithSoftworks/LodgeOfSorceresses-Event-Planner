<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string                             $hash
 * @property string                             $disk
 * @property string                             $path
 * @property string                             $mime
 * @property int                                $size
 * @property string|null                        $metadata
 * @property \Illuminate\Support\Carbon|null    $created_at
 * @property \Illuminate\Support\Carbon|null    $updated_at
 *
 * @property-read EloquentCollection|DpsParse[]         $asInfoScreenshotOfDpsParse
 * @property-read EloquentCollection|DpsParse[]         $asParseScreenshotOfDpsParse
 * @property-read EloquentCollection|\App\Models\User[] $uploaders
 *
 * @method static EloquentBuilder|File newModelQuery()
 * @method static EloquentBuilder|File newQuery()
 * @method static EloquentBuilder|File ofType($type = 'image')
 * @method static EloquentBuilder|File query()
 * @method static EloquentBuilder|File whereCreatedAt($value)
 * @method static EloquentBuilder|File whereDisk($value)
 * @method static EloquentBuilder|File whereHash($value)
 * @method static EloquentBuilder|File whereMetadata($value)
 * @method static EloquentBuilder|File whereMime($value)
 * @method static EloquentBuilder|File wherePath($value)
 * @method static EloquentBuilder|File whereSize($value)
 * @method static EloquentBuilder|File whereUpdatedAt($value)
 */
class File extends Model
{
    protected $table = 'files';

    protected $primaryKey = 'hash';

    public $incrementing = false;

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $type
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Exception
     */
    public function scopeOfType($query, $type = 'image'): \Illuminate\Database\Query\Builder
    {
        if (!in_array($type, ['plain', 'image', 'audio', 'video', 'application'])) {
            throw new \Exception();
        }

        return $query->where('mime', 'like', $type . '/%');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function uploaders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'files_users', 'file_hash', 'user_id')->withTimestamps()->withPivot(['id', 'qquuid', 'original_client_name', 'tag']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asParseScreenshotOfDpsParse(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsParse::class, 'parse_file_hash', 'hash');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asInfoScreenshotOfDpsParse(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DpsParse::class, 'superstar_file_hash', 'hash');
    }
}

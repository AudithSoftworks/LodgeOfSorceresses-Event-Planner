<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * @property string $hash
 * @property string $disk
 * @property string $path
 * @property string $mime
 * @property int $size
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EloquentCollection|DpsParse[] $asInfoScreenshotOfDpsParse
 * @property-read int|null $as_info_screenshot_of_dps_parse_count
 * @property-read EloquentCollection|DpsParse[] $asParseScreenshotOfDpsParse
 * @property-read int|null $as_parse_screenshot_of_dps_parse_count
 * @property-read EloquentCollection|User[] $uploaders
 * @property-read int|null $uploaders_count
 * @method static EloquentBuilder|$this newModelQuery()
 * @method static EloquentBuilder|$this newQuery()
 * @method static EloquentBuilder|$this ofType($type = 'image')
 * @method static EloquentBuilder|$this query()
 * @method static EloquentBuilder|$this whereCreatedAt($value)
 * @method static EloquentBuilder|$this whereDisk($value)
 * @method static EloquentBuilder|$this whereHash($value)
 * @method static EloquentBuilder|$this whereMetadata($value)
 * @method static EloquentBuilder|$this whereMime($value)
 * @method static EloquentBuilder|$this wherePath($value)
 * @method static EloquentBuilder|$this whereSize($value)
 * @method static EloquentBuilder|$this whereUpdatedAt($value)
 */
class File extends Model
{
    protected $table = 'files';

    protected $keyType = 'string';

    protected $primaryKey = 'hash';

    public $incrementing = false;

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $type
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Exception
     */
    public function scopeOfType($query, $type = 'image'): Builder
    {
        $allowedMimes = ['plain', 'image', 'audio', 'video', 'application'];
        if (!in_array($type, $allowedMimes, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid File MIME provided (%s): expected one of [%s]', $type, implode('|', $allowedMimes))
            );
        }

        return $query->where('mime', 'like', $type . '/%');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function uploaders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'files_users', 'file_hash', 'user_id')->withTimestamps()->withPivot(['id', 'qquuid', 'original_client_name', 'tag']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asParseScreenshotOfDpsParse(): HasMany
    {
        return $this->hasMany(DpsParse::class, 'parse_file_hash', 'hash');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asInfoScreenshotOfDpsParse(): HasMany
    {
        return $this->hasMany(DpsParse::class, 'info_file_hash', 'hash');
    }
}

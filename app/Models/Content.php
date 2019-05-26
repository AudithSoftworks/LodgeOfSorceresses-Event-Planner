<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $short_name
 * @property string|null $version
 * @property string $type
 * @property string|null $tier
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $owners
 * @method static Builder|Content newModelQuery()
 * @method static Builder|Content newQuery()
 * @method static Builder|Content query()
 * @method static Builder|Content whereCreatedAt($value)
 * @method static Builder|Content whereId($value)
 * @method static Builder|Content whereName($value)
 * @method static Builder|Content whereShortName($value)
 * @method static Builder|Content whereTier($value)
 * @method static Builder|Content whereType($value)
 * @method static Builder|Content whereUpdatedAt($value)
 * @method static Builder|Content whereVersion($value)
 */
class Content extends Model
{
    public const CACHE_TTL = 604800; // Week in secs

    protected $table = 'content';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function characters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'characters_content', 'content_id', 'character_id')->withTimestamps();
    }
}

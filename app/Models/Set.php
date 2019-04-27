<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property string                          $slug
 * @property string                          $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static EloquentBuilder|Set newModelQuery()
 * @method static EloquentBuilder|Set newQuery()
 * @method static EloquentBuilder|Set query()
 * @method static EloquentBuilder|Set whereId($value)
 * @method static EloquentBuilder|Set whereName($value)
 * @method static EloquentBuilder|Set whereSlug($value)
 * @method static EloquentBuilder|Set whereCreatedAt($value)
 * @method static EloquentBuilder|Set whereUpdatedAt($value)
 */
class Set extends Model
{
    public const CACHE_TTL = 604800; // Week in secs
}

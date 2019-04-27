<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property string                          $slug
 * @property string                          $name
 * @property string                          $type
 * @property string                          $location
 * @property string|null                     $bonus_item_1
 * @property string|null                     $bonus_item_2
 * @property string|null                     $bonus_item_3
 * @property string|null                     $bonus_item_4
 * @property string|null                     $bonus_item_5
 * @property mixed                           $has_jewels
 * @property mixed                           $has_weapons
 * @property mixed                           $has_light_armor
 * @property mixed                           $has_medium_armor
 * @property mixed                           $has_heavy_armor
 * @property mixed|null                      $traits_needed
 * @property mixed                           $pts
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static EloquentBuilder|Set newModelQuery()
 * @method static EloquentBuilder|Set newQuery()
 * @method static EloquentBuilder|Set query()
 * @method static EloquentBuilder|Set whereBonusItem1($value)
 * @method static EloquentBuilder|Set whereBonusItem2($value)
 * @method static EloquentBuilder|Set whereBonusItem3($value)
 * @method static EloquentBuilder|Set whereBonusItem4($value)
 * @method static EloquentBuilder|Set whereBonusItem5($value)
 * @method static EloquentBuilder|Set whereCreatedAt($value)
 * @method static EloquentBuilder|Set whereHasHeavyArmor($value)
 * @method static EloquentBuilder|Set whereHasJewels($value)
 * @method static EloquentBuilder|Set whereHasLightArmor($value)
 * @method static EloquentBuilder|Set whereHasMediumArmor($value)
 * @method static EloquentBuilder|Set whereHasWeapons($value)
 * @method static EloquentBuilder|Set whereId($value)
 * @method static EloquentBuilder|Set whereLocation($value)
 * @method static EloquentBuilder|Set whereName($value)
 * @method static EloquentBuilder|Set wherePts($value)
 * @method static EloquentBuilder|Set whereSlug($value)
 * @method static EloquentBuilder|Set whereTraitsNeeded($value)
 * @method static EloquentBuilder|Set whereType($value)
 * @method static EloquentBuilder|Set whereUpdatedAt($value)
 */
class Set extends Model
{
    public const CACHE_TTL = 604800; // Week in secs
}

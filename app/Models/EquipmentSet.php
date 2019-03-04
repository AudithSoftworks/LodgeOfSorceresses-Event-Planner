<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet query()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentSet whereUpdatedAt($value)
 */
class EquipmentSet extends Model
{
    public const CACHE_TTL = 7 * 86400;

    protected $dates = ['deleted_at'];
}

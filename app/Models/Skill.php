<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property string                          $name
 * @property string                          $slug
 * @property int                             $skill_line
 * @property int|null                        $parent
 * @property mixed                           $type
 * @property string                          $effect_1
 * @property string|null                     $effect_2
 * @property string|null                     $cost
 * @property string                          $icon
 * @property mixed                           $pts
 * @property string|null                     $cast_time
 * @property string|null                     $target
 * @property string|null                     $range
 * @property mixed|null                      $unlocks_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCastTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereEffect1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereEffect2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill wherePts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereSkillLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereUnlocksAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereUpdatedAt($value)
 */
class Skill extends Model
{
    //
}

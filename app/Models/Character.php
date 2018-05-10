<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Character.
 */
class Character extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'class',
        'role',
        'sets',
    ];
}

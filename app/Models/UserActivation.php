<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivation query()
 */
class UserActivation extends Model
{
    protected $table = 'users_activations';

    protected $fillable = [
        'code',
        'completed',
        'completed_at',
    ];

    /**
     * @param  mixed $completed
     *
     * @return bool
     */
    public function getCompleted($completed)
    {
        return (bool)$completed;
    }

    /**
     * @param  mixed $completed
     *
     * @return void
     */
    public function setCompleted($completed)
    {
        $this->attributes['completed'] = (bool)$completed;
    }

    public function getCode()
    {
        return $this->attributes['code'];
    }
}

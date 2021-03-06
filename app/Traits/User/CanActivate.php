<?php

namespace App\Traits\User;

use App\Models\UserActivation;

trait CanActivate
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activations()
    {
        /** @var $this \Eloquent */
        return $this->hasMany(UserActivation::class, 'user_id');
    }
}

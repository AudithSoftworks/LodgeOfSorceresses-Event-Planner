<?php

namespace App\Events\User;

use App\Models\User;

interface GetUserInterface
{
    public function getOwner(): User;
}

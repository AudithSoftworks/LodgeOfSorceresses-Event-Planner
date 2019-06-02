<?php

namespace App\Events\Character;

use App\Models\DpsParse;

interface DpsParseInterface
{
    public function getDpsParse(): DpsParse;
}

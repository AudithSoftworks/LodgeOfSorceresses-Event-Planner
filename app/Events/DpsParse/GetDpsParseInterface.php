<?php

namespace App\Events\DpsParse;

use App\Models\DpsParse;

interface GetDpsParseInterface
{
    public function getDpsParse(): DpsParse;
}

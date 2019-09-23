<?php

namespace App\Events\DpsParse;

interface GetDpsParsesInterface
{
    /**
     * @return iterable|\App\Models\DpsParse[]
     */
    public function getDpsParses();
}

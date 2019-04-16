<?php namespace App\Events\DpsParses;

use App\Models\DpsParse;

class DpsParseApproved
{
    /**
     * @var DpsParse
     */
    public $dpsParse;

    /**
     * @param \App\Models\DpsParse $dpsParse
     */
    public function __construct(DpsParse $dpsParse)
    {
        $this->dpsParse = $dpsParse;
    }
}

<?php namespace App\Events\DpsParse;

use App\Events\Character\DpsParseInterface;
use App\Models\DpsParse;

class DpsParseApproved implements DpsParseInterface
{
    /**
     * @var DpsParse
     */
    public $dpsParse;

    public function __construct(DpsParse $dpsParse)
    {
        $this->dpsParse = $dpsParse;
    }

    public function getDpsParse(): DpsParse
    {
        return $this->dpsParse;
    }
}

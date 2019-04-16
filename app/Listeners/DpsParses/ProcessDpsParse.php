<?php namespace App\Listeners\DpsParses;

use App\Events\DpsParses\DpsParseApproved;

class ProcessDpsParse
{
    /**
     * @param \App\Events\DpsParses\DpsParseApproved $event
     *
     * @return bool
     */
    public function handle(DpsParseApproved $event): bool
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();

        $dpsParseService = app('dps.parse');

        return $dpsParseService->process($dpsParse);
    }
}

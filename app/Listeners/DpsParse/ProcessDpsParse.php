<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseApproved;

class ProcessDpsParse
{
    /**
     * @param \App\Events\DpsParse\DpsParseApproved $event
     *
     * @return bool
     */
    public function handle(DpsParseApproved $event): bool
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();

        $guildRankingService = app('guild.ranks.clearance');

        return $guildRankingService->processDpsParse($dpsParse);
    }
}

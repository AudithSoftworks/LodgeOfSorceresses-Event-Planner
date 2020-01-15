<?php namespace App\Listeners\Cache;

use App\Events\Team\TeamNeedsRecacheInterface;
use Illuminate\Support\Facades\Cache;

class DeleteTeamCache
{
    /**
     * @param \App\Events\Team\TeamNeedsRecacheInterface $event
     *
     * @return bool
     */
    public function handle(TeamNeedsRecacheInterface $event): bool
    {
        $team = $event->getTeam();
        Cache::forget('team-' . $team->id);

        return true;
    }
}

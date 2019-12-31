<?php namespace App\Listeners\Cache;

use App\Events\Team\TeamNeedsRecacheInterface;

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
        app('cache.store')->forget('team-' . $team->id);

        return true;
    }
}

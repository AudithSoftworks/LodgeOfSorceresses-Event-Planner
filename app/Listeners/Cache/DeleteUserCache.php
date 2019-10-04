<?php namespace App\Listeners\Cache;

use App\Events\User\UserNeedsRecacheInterface;

class DeleteUserCache
{
    /**
     * @param \App\Events\User\UserNeedsRecacheInterface $event
     *
     * @return bool
     */
    public function handle(UserNeedsRecacheInterface $event): bool
    {
        $user = $event->getOwner();
        app('cache.store')->forget('user-' . $user->id);

        return true;
    }
}

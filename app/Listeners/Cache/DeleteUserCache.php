<?php namespace App\Listeners\Cache;

use App\Events\User\UserNeedsRecacheInterface;
use Illuminate\Support\Facades\Cache;

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
        $user !== null && Cache::forget('user-' . $user->id);

        return true;
    }
}

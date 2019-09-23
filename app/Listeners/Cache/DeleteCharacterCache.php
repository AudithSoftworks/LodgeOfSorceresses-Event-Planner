<?php namespace App\Listeners\Cache;

use App\Events\Character\CharacterNeedsRecacheInterface;

class DeleteCharacterCache
{
    /**
     * @param \App\Events\Character\CharacterNeedsRecacheInterface $event
     *
     * @return bool
     */
    public function handle(CharacterNeedsRecacheInterface $event): bool
    {
        $character = $event->getCharacter();
        app('cache.store')->forget('character-' . $character->id);

        return true;
    }
}

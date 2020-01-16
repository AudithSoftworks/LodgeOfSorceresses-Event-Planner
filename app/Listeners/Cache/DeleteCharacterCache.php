<?php namespace App\Listeners\Cache;

use App\Events\Character\CharacterNeedsRecacheInterface;
use Illuminate\Support\Facades\Cache;

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
        Cache::forget('character-' . $character->id);

        return true;
    }
}

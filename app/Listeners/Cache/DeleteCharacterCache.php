<?php namespace App\Listeners\Cache;

use App\Events\Character\CharacterNeedsRecacheInterface;
use Illuminate\Support\Facades\Cache;

class DeleteCharacterCache
{
    public function handle(CharacterNeedsRecacheInterface $event): bool
    {
        $character = $event->getCharacter();
        Cache::forget('character-' . $character->id);

        return true;
    }
}

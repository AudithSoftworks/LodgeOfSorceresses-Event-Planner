<?php namespace App\Listeners\Cache;

use App\Models\Character;
use App\Models\Content;
use App\Models\Set;
use App\Models\Skill;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Traits\Characters\HasOrIsDpsParse;
use App\Traits\Characters\IsCharacter;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recache
{
    use HasOrIsDpsParse, IsCharacter;

    /**
     * @param \Illuminate\Cache\Events\CacheMissed $event
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(CacheMissed $event)
    {
        switch ($cacheKey = $event->key) {
            case 'sets':
                $recache = [
                    'data' => Set::query()->get()->keyBy('id')->toArray(),
                    'ttl' => Set::CACHE_TTL
                ];
                break;
            case 'skills':
                $recache = [
                    'data' => Skill::query()->get()->keyBy('id')->toArray(),
                    'ttl' => Set::CACHE_TTL
                ];
                break;
            case 'content':
                $recache = [
                    'data' => Content::query()->orderBy('tier')->get()->keyBy('id')->toArray(),
                    'ttl' => Content::CACHE_TTL
                ];
                break;
            default:
                $recache = [
                    'data' => $this->handleComplexCache($cacheKey),
                    'ttl' => null,
                ];
        }
        if (empty($recache)) {
            return null;
        }

        app('cache.store')->put($cacheKey, $recache['data'], $recache['ttl']);

        return $recache['data'];
    }

    private function handleComplexCache(string $key)
    {
        if (strpos($key, 'character-') === 0) {
            $characterId = preg_replace('/\D/', '', $key);

            return !empty($characterId) ? $this->handleCharacterItem((int)$characterId) : [];
        }

        return null;
    }

    private function handleCharacterItem(int $characterId): ?Character
    {
        $character = Character::query()
            ->with([
                'dpsParses' => static function (HasMany $query) {
                    $query->orderBy('id', 'desc')->limit(10);
                },
                'content',
                'owner'
            ])
            ->whereId($characterId)
            ->first();
        if ($character) {
            $character->class = ClassTypes::getClassName($character->class);
            $character->role = RoleTypes::getRoleName($character->role);
            $character->sets = $this->parseCharacterSets($character);
            $character->skills = $this->parseCharacterSkills($character);
            $this->processDpsParses($character);

            return $character;
        }

        return null;
    }
}

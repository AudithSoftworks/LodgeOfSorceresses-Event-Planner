<?php namespace App\Listeners\Cache;

use App\Models\Content;
use App\Models\Set;
use App\Models\Skill;
use Illuminate\Cache\Events\CacheMissed;

class Recache
{
    /**
     * @param \Illuminate\Cache\Events\CacheMissed $event
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(CacheMissed $event)
    {
        $recache = [];
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
        }
        if (empty($recache)) {
            return null;
        }

        app('cache.store')->put($cacheKey, $recache['data'], $recache['ttl']);

        return $recache['data'];
    }
}

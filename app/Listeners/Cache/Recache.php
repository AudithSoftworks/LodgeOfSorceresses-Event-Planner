<?php namespace App\Listeners\Cache;

use App\Models\Set;
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
        }
        if (empty($recache)) {
            return null;
        }

        app('cache.store')->put($cacheKey, $recache['data'], $recache['ttl']);

        return $recache['data'];
    }
}

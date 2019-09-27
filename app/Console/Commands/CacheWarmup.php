<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Cache\Repository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CacheWarmup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warms-up application cache.';

    public function handle(): void
    {
        $this->info('Starting cache-warmup...');
        $cacheService = app('cache.store');
        $this->info('Cache cleared.');
        $cacheService->clear();
        $cacheService->has('sets'); // has() triggers CacheMissed event, which in return triggers Recache mechanism.
        $this->info('Sets successfully cached!');
        $cacheService->has('skills');
        $this->info('Skills successfully cached!');
        $cacheService->has('content');
        $this->info('Content successfully cached!');
        $this->cacheAllCharacters($cacheService);
        $this->info('Characters successfully cached!');
        $this->info('Cache-warmup successfully finished!');
    }

    private function cacheAllCharacters(Repository $cacheService): void
    {
        $characters = Character::query()
            ->with([
                'owner' => static function (BelongsTo $query) {
                    $query->whereNotNull('name');
                },
            ])
            ->get(['id']);
        $characterIds = $characters->pluck('id');
        foreach ($characterIds as $characterId) {
            $cacheService->has('character-' . $characterId);
        }
    }
}
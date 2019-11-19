<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Cache\Repository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulateCypressFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixture:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warms-up application cache.';

    public function handle(): void
    {
        $this->info('Starting populating Cypress fixtures...');
        $cacheService = app('cache.store');
        $cacheService->has('sets'); // has() triggers CacheMissed event, which in return triggers Recache mechanism.
        $setsCacheAsJson = json_encode($cacheService->get('sets'), JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.sets.json'), $setsCacheAsJson);

        $cacheService->has('skills');
        $skillsCacheAsJson = json_encode($cacheService->get('skills'), JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.skills.json'), $skillsCacheAsJson);

        $this->info('Cypress fixture population complete.');
    }
}

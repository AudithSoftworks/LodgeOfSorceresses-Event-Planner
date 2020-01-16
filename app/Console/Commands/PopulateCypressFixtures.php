<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
    protected $description = 'Populates JSON fixtures for Cypress E2E testing.';

    public function handle(): void
    {
        $this->info('Starting populating Cypress fixtures...');

        Cache::has('sets'); // has() triggers CacheMissed event, which in return triggers Recache mechanism.
        $setsCacheAsJson = json_encode(Cache::get('sets'), JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.sets.json'), $setsCacheAsJson);

        Cache::has('skills');
        $skillsCacheAsJson = json_encode(Cache::get('skills'), JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.skills.json'), $skillsCacheAsJson);

        $this->info('Cypress fixture population complete.');
    }
}

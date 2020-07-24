<?php

namespace App\Console\Commands;

use App\Http\Controllers\OnboardingController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PopulateCypressFixtures extends Command
{
    /**
     * @var string
     */
    protected $signature = 'cypress:fixture:populate';

    /**
     * @var string
     */
    protected $description = 'Populates JSON fixtures for Cypress E2E testing.';

    /**
     * @throws \JsonException
     */
    public function handle(): void
    {
        $this->info('Starting populating Cypress fixtures...');

        Cache::has('sets'); // Recache trigger.
        $setsCacheAsJson = json_encode(Cache::get('sets'), JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.sets.json'), $setsCacheAsJson);

        Cache::has('skills'); // Recache trigger.
        $skillsCacheAsJson = json_encode(Cache::get('skills'), JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.skills.json'), $skillsCacheAsJson);

        Cache::has('content'); // Recache trigger.
        $contentCacheAsJson = json_encode(Cache::get('content'), JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        file_put_contents(base_path('./cypress/fixtures/.content.json'), $contentCacheAsJson);

        $this->info('Cypress fixture population complete.');
    }
}

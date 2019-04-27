<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use PathfinderMediaGroup\ApiLibrary\Api\SetApi;
use PathfinderMediaGroup\ApiLibrary\Auth\TokenAuth;

class FetchSetsViaPmgApi extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pmg:sets';

    /**
     * @var string
     */
    protected $description = 'Fetches ESO sets from PMG API.';

    /**
     * @throws \PathfinderMediaGroup\ApiLibrary\Exception\FailedPmgRequestException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(): void
    {
        $botAccessToken = config('services.pmg.api_token');
        $tokenAuthObj = new TokenAuth($botAccessToken);
        $setApi = new SetApi($tokenAuthObj);
        $sets = $setApi->getAll();
        $this->syncSets($sets);

        app('cache.store')->delete('sets');

        $this->info('Sets succesfully synced and Cache cleared!');
    }

    /**
     * @param array $data
     */
    private function syncSets(array $data): void
    {
        app('db.connection')->table('sets')->truncate();
        app('db.connection')->table('sets')->insert($data);
        app('db.connection')->table('sets')->update(['created_at' => Carbon::now()]);
    }
}

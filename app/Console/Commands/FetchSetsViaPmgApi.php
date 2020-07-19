<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
     */
    public function handle(): void
    {
        $botAccessToken = config('services.pmg.api_token');
        $tokenAuthObj = new TokenAuth($botAccessToken);
        $setApi = new SetApi($tokenAuthObj);
        $sets = $setApi->getAll();
        $this->syncSets($sets);

        Cache::forget('sets');

        $this->info('Sets succesfully synced and Cache cleared!');
    }

    /**
     * @param array $data
     */
    private function syncSets(array $data): void
    {
        DB::table('sets')->truncate();
        DB::table('sets')->insert($data);
        DB::table('sets')->update(['created_at' => Carbon::now()]);
    }
}

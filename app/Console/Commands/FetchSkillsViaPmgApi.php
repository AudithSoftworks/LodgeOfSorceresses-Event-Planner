<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PathfinderMediaGroup\ApiLibrary\Api\SkillApi;
use PathfinderMediaGroup\ApiLibrary\Auth\TokenAuth;

class FetchSkillsViaPmgApi extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pmg:skills';

    /**
     * @var string
     */
    protected $description = 'Fetches ESO skill list from PMG API.';

    /**
     * @throws \PathfinderMediaGroup\ApiLibrary\Exception\FailedPmgRequestException
     */
    public function handle(): void
    {
        $botAccessToken = config('services.pmg.api_token');
        $tokenAuthObj = new TokenAuth($botAccessToken);
        $skillApi = new SkillApi($tokenAuthObj);
        $skills = $skillApi->getAll();
        foreach ($skills as &$skill) {
            $skill['skill_line'] = $skill['skillline'];
            unset($skill['skillline']);
        }
        unset($skill);
        $this->syncSkills($skills);

        Cache::forget('skills');

        $this->info('Skills succesfully synced and Cache cleared!');
    }

    /**
     * @param array $data
     */
    private function syncSkills(array $data): void
    {
        DB::table('skills')->truncate();
        DB::table('skills')->insert($data);
        DB::table('skills')->update(['created_at' => Carbon::now()]);
    }
}

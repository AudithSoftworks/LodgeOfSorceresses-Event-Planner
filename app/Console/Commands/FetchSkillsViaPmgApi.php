<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
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

        app('cache.store')->delete('skills');

        $this->info('Skills succesfully synced and Cache cleared!');
    }

    /**
     * @param array $data
     */
    private function syncSkills(array $data): void
    {
        app('db.connection')->table('skills')->truncate();
        app('db.connection')->table('skills')->insert($data);
        app('db.connection')->table('skills')->update(['created_at' => Carbon::now()]);
    }
}

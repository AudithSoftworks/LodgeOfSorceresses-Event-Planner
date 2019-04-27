<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

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

    public function handle(): void
    {
        $skills = app('pmg.api')->getAllSkills();
        foreach ($skills as &$skill) {
            $skill['skill_line'] = $skill['skillline'];
            unset($skill['skillline']);
        }
        unset($skill);
        $this->syncSkills($skills);
        $this->info('Skills succesfully synced!');
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

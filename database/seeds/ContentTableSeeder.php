<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        app('db.connection')->table('content')->truncate();
        $data = [
            ['id' => 1, 'name' => 'Veteran Aetherium Archive', 'short_name' => 'vAA', 'version' => null, 'type' => 'endgame', 'tier' => 1],
            ['id' => 2, 'name' => 'Veteran Aetherium Archive', 'short_name' => 'vAA', 'version' => 'HM', 'type' => 'endgame', 'tier' => 2],
            ['id' => 3, 'name' => 'Veteran Hel Ra Citadel', 'short_name' => 'vHRC', 'version' => null, 'type' => 'endgame', 'tier' => 1],
            ['id' => 4, 'name' => 'Veteran Hel Ra Citadel', 'short_name' => 'vHRC', 'version' => 'HM', 'type' => 'endgame', 'tier' => 2],
            ['id' => 5, 'name' => 'Veteran Sanctum Ophidia', 'short_name' => 'vSO', 'version' => null, 'type' => 'endgame', 'tier' => 1],
            ['id' => 6, 'name' => 'Veteran Sanctum Ophidia', 'short_name' => 'vSO', 'version' => 'HM', 'type' => 'endgame', 'tier' => 2],
            ['id' => 7, 'name' => 'Veteran Maw of Lorkhaj', 'short_name' => 'vMOL', 'version' => null, 'type' => 'endgame', 'tier' => 2],
            ['id' => 8, 'name' => 'Veteran Maw of Lorkhaj', 'short_name' => 'vMOL', 'version' => 'HM', 'type' => 'endgame', 'tier' => 4],
            ['id' => 9, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => null, 'type' => 'endgame', 'tier' => 2],
            ['id' => 10, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => '+1 Poison', 'type' => 'endgame', 'tier' => 3],
            ['id' => 11, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => '+1 Assassin', 'type' => 'endgame', 'tier' => 3],
            ['id' => 12, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => '+2', 'type' => 'endgame', 'tier' => 4],
            ['id' => 13, 'name' => 'Veteran Halls of Fabrication', 'short_name' => 'vHOF', 'version' => null, 'type' => 'endgame', 'tier' => 3],
            ['id' => 14, 'name' => 'Veteran Halls of Fabrication', 'short_name' => 'vHOF', 'version' => 'HM', 'type' => 'endgame', 'tier' => 4],
            ['id' => 15, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => null, 'type' => 'endgame', 'tier' => 2],
            ['id' => 16, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+1 Fire', 'type' => 'endgame', 'tier' => 3],
            ['id' => 17, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+1 Lightning', 'type' => 'endgame', 'tier' => 3],
            ['id' => 18, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+1 Ice', 'type' => 'endgame', 'tier' => 3],
            ['id' => 19, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+2 Fire & Ice', 'type' => 'endgame', 'tier' => 4],
            ['id' => 20, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+2 Fire & Lightning', 'type' => 'endgame', 'tier' => 4],
            ['id' => 21, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+2 Ice & Lightning', 'type' => 'endgame', 'tier' => 4],
            ['id' => 22, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+3', 'type' => 'endgame', 'tier' => 4],
            ['id' => 23, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => null, 'type' => 'endgame', 'tier' => 3],
            ['id' => 24, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => 'Fire HM', 'type' => 'endgame', 'tier' => 4],
            ['id' => 25, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => 'Ice HM', 'type' => 'endgame', 'tier' => 4],
            ['id' => 26, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => 'Final HM', 'type' => 'endgame', 'tier' => 4],
        ];
        app('db.connection')->table('content')->insert($data);
        Schema::enableForeignKeyConstraints();
        app('db.connection')->table('content')->update(['created_at' => Carbon::now()]);
    }
}

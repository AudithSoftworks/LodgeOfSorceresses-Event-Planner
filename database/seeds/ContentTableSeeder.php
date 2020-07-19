<?php

use App\Services\GuildRanksAndClearance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

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
            ['id' => 1, 'name' => 'Veteran Aetherium Archive', 'short_name' => 'vAA', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 2, 'name' => 'Veteran Aetherium Archive', 'short_name' => 'vAA', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 3, 'name' => 'Veteran Hel Ra Citadel', 'short_name' => 'vHRC', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 4, 'name' => 'Veteran Hel Ra Citadel', 'short_name' => 'vHRC', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 5, 'name' => 'Veteran Sanctum Ophidia', 'short_name' => 'vSO', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 6, 'name' => 'Veteran Sanctum Ophidia', 'short_name' => 'vSO', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 7, 'name' => 'Veteran Maw of Lorkhaj', 'short_name' => 'vMOL', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 8, 'name' => 'Veteran Maw of Lorkhaj', 'short_name' => 'vMOL', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 9, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 10, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => '+1 Poison', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 11, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => '+1 Assassin', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 12, 'name' => 'Veteran Asylum Sanctorium', 'short_name' => 'vAS', 'version' => '+2', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 13, 'name' => 'Veteran Halls of Fabrication', 'short_name' => 'vHOF', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 14, 'name' => 'Veteran Halls of Fabrication', 'short_name' => 'vHOF', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 15, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 16, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+1 Fire', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 17, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+1 Lightning', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 18, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+1 Ice', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 19, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+2 Fire & Ice', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 20, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+2 Fire & Lightning', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 21, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+2 Ice & Lightning', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 22, 'name' => 'Veteran Cloudrest', 'short_name' => 'vCR', 'version' => '+3', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 23, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 24, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => 'Fire HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 25, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => 'Ice HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 26, 'name' => 'Veteran Sunspire', 'short_name' => 'vSS', 'version' => 'Final HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 27, 'name' => 'Veteran Kyne\'s Aegis', 'short_name' => 'vKA', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 28, 'name' => 'Veteran Kyne\'s Aegis', 'short_name' => 'vKA HM', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],

            ['id' => 29, 'name' => 'Veteran Imperial City Prison', 'short_name' => 'vICP', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 30, 'name' => 'Veteran White Gold Tower', 'short_name' => 'vWGT', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 31, 'name' => 'Veteran Cradle of Shadows', 'short_name' => 'vCOS', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 32, 'name' => 'Veteran Ruins of Mazzatun', 'short_name' => 'vROM', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 33, 'name' => 'Veteran Imperial City Prison', 'short_name' => 'vICP HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 34, 'name' => 'Veteran White Gold Tower', 'short_name' => 'vWGT HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 35, 'name' => 'Veteran Cradle of Shadows', 'short_name' => 'vCOS HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 36, 'name' => 'Veteran Ruins of Mazzatun', 'short_name' => 'vROM HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 37, 'name' => 'Veteran Bloodroot Forge', 'short_name' => 'vBRF', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 38, 'name' => 'Veteran Bloodroot Forge', 'short_name' => 'vBRF HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 39, 'name' => 'Veteran Falkreath Hold', 'short_name' => 'vFH', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 40, 'name' => 'Veteran Falkreath Hold', 'short_name' => 'vFH HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 41, 'name' => 'Veteran Scalecaller Peak', 'short_name' => 'vSCP', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 42, 'name' => 'Veteran Scalecaller Peak', 'short_name' => 'vSCP HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 43, 'name' => 'Veteran Fang Lair', 'short_name' => 'vFL', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 44, 'name' => 'Veteran Fang Lair', 'short_name' => 'vFL HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 45, 'name' => 'Veteran Moonhunter Keep', 'short_name' => 'vMHK', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 46, 'name' => 'Veteran Moonhunter Keep', 'short_name' => 'vMHK HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 47, 'name' => 'Veteran March of Sacrifices', 'short_name' => 'vMOS', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 48, 'name' => 'Veteran March of Sacrifices', 'short_name' => 'vMOS HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 49, 'name' => 'Veteran Depths of Malatar', 'short_name' => 'vDOM', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 50, 'name' => 'Veteran Depths of Malatar', 'short_name' => 'vDOM HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 51, 'name' => 'Veteran Frostvault', 'short_name' => 'vFV', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 52, 'name' => 'Veteran Frostvault', 'short_name' => 'vFV HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 53, 'name' => 'Veteran Moongrave Fane', 'short_name' => 'vMF', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 54, 'name' => 'Veteran Moongrave Fane', 'short_name' => 'vMF HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 55, 'name' => 'Veteran Lair of Maarselok', 'short_name' => 'vLOM', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 56, 'name' => 'Veteran Lair of Maarselok', 'short_name' => 'vLOM HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 57, 'name' => 'Veteran Icereach', 'short_name' => 'vIR', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 58, 'name' => 'Veteran Icereach', 'short_name' => 'vIR HM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],

            ['id' => 60, 'name' => 'Veteran Dragonstar Arena', 'short_name' => 'vDSA', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 61, 'name' => 'Veteran Blackrose Prison Arena', 'short_name' => 'vBRP', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
        ];
        app('db.connection')->table('content')->insert($data);
        Schema::enableForeignKeyConstraints();
        app('db.connection')->table('content')->update(['created_at' => Carbon::now()]);
    }
}

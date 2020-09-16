<?php

namespace Database\Seeders;

use App\Services\GuildRanksAndClearance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContentTableSeeder extends Seeder
{
    public const vAA = 'Veteran Aetherium Archive';

    public const vHRC = 'Veteran Hel Ra Citadel';

    public const vSO = 'Veteran Sanctum Ophidia';

    public const vMOL = 'Veteran Maw of Lorkhaj';

    public const vAS = 'Veteran Asylum Sanctorium';

    public const vHOF = 'Veteran Halls of Fabrication';

    public const vCR = 'Veteran Cloudrest';

    public const vSS = 'Veteran Sunspire';

    public const vKA = 'Veteran Kyne\'s Aegis';

    public const vICP = 'Veteran Imperial City Prison';

    public const vWGT = 'Veteran White-Gold Tower';

    public const vCOS = 'Veteran Cradle of Shadows';

    public const vROM = 'Veteran Ruins of Mazzatun';

    public const vBRF = 'Veteran Bloodroot Forge';

    public const vFH = 'Veteran Falkreath Hold';

    public const vSCP = 'Veteran Scalecaller Peak';

    public const vFL = 'Veteran Fang Lair';

    public const vMHK = 'Veteran Moonhunter Keep';

    public const vMOS = 'Veteran March of Sacrifices';

    public const vDOM = 'Veteran Depths of Malatar';

    public const vFV = 'Veteran Frostvault';

    public const vMGF = 'Veteran Moongrave Fane';

    public const vLOM = 'Veteran Lair of Maarselok';

    public const vIR = 'Veteran Icereach';

    public const vUHG = 'Veteran Unhallowed Grave';

    public const vCT = 'Veteran Castle Thorn';

    public const vSG = 'Veteran Stone Garden';

    public const vDSA = 'Veteran Dragonstar Arena';

    public const vBRP = 'Veteran Blackrose Prison Arena';

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('content')->truncate();
        $data = [
            ['id' => 1, 'name' => self::vAA, 'short_name' => 'vAA', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 2, 'name' => self::vAA, 'short_name' => 'vAA', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 3, 'name' => self::vHRC, 'short_name' => 'vHRC', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 4, 'name' => self::vHRC, 'short_name' => 'vHRC', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 5, 'name' => self::vSO, 'short_name' => 'vSO', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 6, 'name' => self::vSO, 'short_name' => 'vSO', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 7, 'name' => self::vMOL, 'short_name' => 'vMOL', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 8, 'name' => self::vMOL, 'short_name' => 'vMOL', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 9, 'name' => self::vAS, 'short_name' => 'vAS', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 10, 'name' => self::vAS, 'short_name' => 'vAS', 'version' => '+1 Poison', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 11, 'name' => self::vAS, 'short_name' => 'vAS', 'version' => '+1 Assassin', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 12, 'name' => self::vAS, 'short_name' => 'vAS', 'version' => '+2', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 13, 'name' => self::vHOF, 'short_name' => 'vHOF', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 14, 'name' => self::vHOF, 'short_name' => 'vHOF', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 15, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 16, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+1 Fire', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 17, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+1 Lightning', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 18, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+1 Ice', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 19, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+2 Fire & Ice', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 20, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+2 Fire & Lightning', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 21, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+2 Ice & Lightning', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 22, 'name' => self::vCR, 'short_name' => 'vCR', 'version' => '+3', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 23, 'name' => self::vSS, 'short_name' => 'vSS', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 24, 'name' => self::vSS, 'short_name' => 'vSS', 'version' => 'Fire HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 25, 'name' => self::vSS, 'short_name' => 'vSS', 'version' => 'Ice HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 26, 'name' => self::vSS, 'short_name' => 'vSS', 'version' => 'Final HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],
            ['id' => 27, 'name' => self::vKA, 'short_name' => 'vKA', 'version' => null, 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 28, 'name' => self::vKA, 'short_name' => 'vKA', 'version' => 'HM', 'type' => 'endgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_4],

            ['id' => 29, 'name' => self::vICP, 'short_name' => 'vICP', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 30, 'name' => self::vWGT, 'short_name' => 'vWGT', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 31, 'name' => self::vCOS, 'short_name' => 'vCOS', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 32, 'name' => self::vROM, 'short_name' => 'vROM', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 33, 'name' => self::vICP, 'short_name' => 'vICP', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 34, 'name' => self::vWGT, 'short_name' => 'vWGT', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 35, 'name' => self::vCOS, 'short_name' => 'vCOS', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 36, 'name' => self::vROM, 'short_name' => 'vROM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_1],
            ['id' => 37, 'name' => self::vBRF, 'short_name' => 'vBRF', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 38, 'name' => self::vBRF, 'short_name' => 'vBRF', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 39, 'name' => self::vFH, 'short_name' => 'vFH', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 40, 'name' => self::vFH, 'short_name' => 'vFH', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 41, 'name' => self::vSCP, 'short_name' => 'vSCP', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 42, 'name' => self::vSCP, 'short_name' => 'vSCP', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 43, 'name' => self::vFL, 'short_name' => 'vFL', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 44, 'name' => self::vFL, 'short_name' => 'vFL', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 45, 'name' => self::vMHK, 'short_name' => 'vMHK', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 46, 'name' => self::vMHK, 'short_name' => 'vMHK', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 47, 'name' => self::vMOS, 'short_name' => 'vMOS', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 48, 'name' => self::vMOS, 'short_name' => 'vMOS', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 49, 'name' => self::vDOM, 'short_name' => 'vDOM', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 50, 'name' => self::vDOM, 'short_name' => 'vDOM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 51, 'name' => self::vFV, 'short_name' => 'vFV', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 52, 'name' => self::vFV, 'short_name' => 'vFV', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 53, 'name' => self::vMGF, 'short_name' => 'vMF', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 54, 'name' => self::vMGF, 'short_name' => 'vMF', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 55, 'name' => self::vLOM, 'short_name' => 'vLOM', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 56, 'name' => self::vLOM, 'short_name' => 'vLOM', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 57, 'name' => self::vIR, 'short_name' => 'vIR', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 58, 'name' => self::vIR, 'short_name' => 'vIR', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 59, 'name' => self::vUHG, 'short_name' => 'vUG', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 62, 'name' => self::vUHG, 'short_name' => 'vUG', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 63, 'name' => self::vCT, 'short_name' => 'vCS', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 64, 'name' => self::vCT, 'short_name' => 'vCS', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
            ['id' => 65, 'name' => self::vSG, 'short_name' => 'vSG', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 66, 'name' => self::vSG, 'short_name' => 'vSG', 'version' => 'HM', 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],

            ['id' => 60, 'name' => self::vDSA, 'short_name' => 'vDSA', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_2],
            ['id' => 61, 'name' => self::vBRP, 'short_name' => 'vBRP', 'version' => null, 'type' => 'midgame', 'tier' => GuildRanksAndClearance::CLEARANCE_TIER_3],
        ];
        DB::table('content')->insert($data);
        Schema::enableForeignKeyConstraints();
        DB::table('content')->update(['created_at' => Carbon::now()]);
    }
}

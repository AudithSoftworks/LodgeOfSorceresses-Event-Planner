<?php namespace App\Http\Controllers;

use App\Services\GuildRanksAndClearance;
use Illuminate\Http\JsonResponse;

class GroupsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $rankDefinitions = [
            'SOULSHRIVEN' => GuildRanksAndClearance::RANK_SOULSHRIVEN,
            'INITIATE' => GuildRanksAndClearance::RANK_INITIATE,
            'NEOPHYTE' => GuildRanksAndClearance::RANK_NEOPHYTE,
            'PRACTICUS' => GuildRanksAndClearance::RANK_PRACTICUS,
            'ADEPTUS_MINOR' => GuildRanksAndClearance::RANK_ADEPTUS_MINOR,
            'ADEPTUS_MAJOR' => GuildRanksAndClearance::RANK_ADEPTUS_MAJOR,
            'DOMINUS_LIMINIS' => GuildRanksAndClearance::RANK_DOMINUS_LIMINIS,
            'ADEPTUS_EXEMPTUS' => GuildRanksAndClearance::RANK_ADEPTUS_EXEMPTUS,
            'MAGISTER_TEMPLI' => GuildRanksAndClearance::RANK_MAGISTER_TEMPLI,
            'IPSISSIMUS' => GuildRanksAndClearance::RANK_IPSISSIMUS,
        ];

        return response()->json($rankDefinitions);
    }
}

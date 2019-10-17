<?php namespace App\Http\Controllers;

use App\Services\GuildRankAndClearance;
use Illuminate\Http\JsonResponse;

class GroupsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $rankDefinitions = [
            'SOULSHRIVEN' => GuildRankAndClearance::RANK_SOULSHRIVEN,
            'INITIATE' => GuildRankAndClearance::RANK_INITIATE,
            'NEOPHYTE' => GuildRankAndClearance::RANK_NEOPHYTE,
            'PRACTICUS' => GuildRankAndClearance::RANK_PRACTICUS,
            'ADEPTUS_MINOR' => GuildRankAndClearance::RANK_ADEPTUS_MINOR,
            'ADEPTUS_MAJOR' => GuildRankAndClearance::RANK_ADEPTUS_MAJOR,
            'DOMINUS_LIMINIS' => GuildRankAndClearance::RANK_DOMINUS_LIMINIS,
            'ADEPTUS_EXEMPTUS' => GuildRankAndClearance::RANK_ADEPTUS_EXEMPTUS,
            'MAGISTER_TEMPLI' => GuildRankAndClearance::RANK_MAGISTER_TEMPLI,
            'IPSISSIMUS' => GuildRankAndClearance::RANK_IPSISSIMUS,
        ];

        return response()->json($rankDefinitions);
    }
}

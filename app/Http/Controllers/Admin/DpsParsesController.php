<?php

namespace App\Http\Controllers\Admin;

use App\Events\DpsParses\DpsParseApproved;
use App\Events\DpsParses\DpsParseDisapproved;
use App\Http\Controllers\Controller;
use App\Models\DpsParse;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use UnexpectedValueException;

class DpsParsesController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('admin', DpsParse::class);
        $dpsParses = DpsParse::query()
            ->with(['owner', 'character'])
            ->whereNull('processed_by')
            ->orderBy('id', 'desc')
            ->get();
        if ($dpsParses->count()) {
            app('cache.store')->has('equipmentSets'); // Trigger Recache listener.
            $equipmentSets = app('cache.store')->get('equipmentSets');
            foreach ($dpsParses as $dpsParse) {
                $characterEquipmentSets = array_filter($equipmentSets, static function ($key) use ($dpsParse) {
                    return in_array($key, explode(',', $dpsParse->sets), false);
                }, ARRAY_FILTER_USE_KEY);
                $dpsParse->sets = array_values($characterEquipmentSets);
                $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
                $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
                if (!$parseFile || !$superstarFile) {
                    throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
                }
                $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
                $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
            }
        }

        return response()->json(['dpsParses' => $dpsParses]);
    }

    /**
     * @param int $parse
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(int $parse): JsonResponse
    {
        $this->authorize('admin', DpsParse::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        $dpsParse = DpsParse::whereId($parse)->firstOrFail();

        app('events')->dispatch(new DpsParseApproved($dpsParse));

        $dpsParse->processed_by = $me->id;
        $dpsParse->save();

        return response()->json(['message' => 'Parse approved!']);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $parse
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Request $request, int $parse): JsonResponse
    {
        $this->authorize('admin', DpsParse::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        $dpsParse = DpsParse::whereId($parse)->firstOrFail();
        $dpsParse->reason_for_disapproval = $request->get('reason_for_disapproval');
        $dpsParse->processed_by = $me->id;
        $dpsParse->save();

        $dpsParse->delete();

        app('events')->dispatch(new DpsParseDisapproved($dpsParse));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

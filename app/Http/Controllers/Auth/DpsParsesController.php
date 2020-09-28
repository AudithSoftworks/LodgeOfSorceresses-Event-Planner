<?php

/**
 * @noinspection PhpUnusedParameterInspection
 * @todo         Remove this when upgrading to PHP-8, where we can have argument types without arguments
 */

namespace App\Http\Controllers\Auth;

use App\Events\DpsParse\DpsParseDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DpsParsesRequests;
use App\Models\Character;
use App\Models\DpsParse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class DpsParsesController extends Controller
{
    /**
     * @param \App\Http\Requests\Auth\DpsParsesRequests $request
     * @param \App\Models\Character $character
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DpsParsesRequests $request, Character $character): JsonResponse
    {
        $dpsParse = new DpsParse();
        $dpsParse->user_id = app('auth.driver')->id();
        $dpsParse->character_id = $character->id;
        $dpsParse->dps_amount = $request->get('dps_amount');
        $dpsParse->parse_file_hash = $request->get('parse_file_hash');
        $dpsParse->info_file_hash = $request->get('info_file_hash');
        $dpsParse->sets = implode(',', $request->get('sets'));
        $dpsParse->save();

        Cache::has('character-' . $dpsParse->character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $dpsParse->character->id), JsonResponse::HTTP_CREATED);
    }

    /**
     * @param \App\Http\Requests\Auth\DpsParsesRequests $request
     * @param \App\Models\Character $character
     * @param \App\Models\DpsParse $dpsParse
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DpsParsesRequests $request, Character $character, DpsParse $dpsParse): JsonResponse
    {
        if ($dpsParse->processed_by !== null || $dpsParse->owner->id !== Auth::id()) {
            throw new ModelNotFoundException('Parse not found (or already processed)!');
        }

        $dpsParse->delete();
        Event::dispatch(new DpsParseDeleted($dpsParse));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

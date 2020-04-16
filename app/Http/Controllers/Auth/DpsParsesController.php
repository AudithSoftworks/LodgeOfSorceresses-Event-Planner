<?php

namespace App\Http\Controllers\Auth;

use App\Events\DpsParse\DpsParseDeleted;
use App\Events\DpsParse\DpsParseSubmitted;
use App\Http\Controllers\Controller;
use App\Models\DpsParse;
use App\Models\User;
use App\Traits\Character\HasOrIsDpsParse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DpsParsesController extends Controller
{
    use HasOrIsDpsParse;

    /**
     * @param int $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);
        $dpsParses = DpsParse::query()
            ->whereUserId(app('auth.driver')->id())
            ->whereCharacterId($characterId)
            ->whereNull('processed_by')
            ->orderBy('id', 'desc')
            ->get();
        foreach ($dpsParses as $dpsParse) {
            $dpsParse->sets = $this->parseDpsParseSets($dpsParse);
            $this->parseScreenshotFiles($dpsParse);
        }

        return response()->json(['dpsParses' => $dpsParses]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);
        $validator = Validator::make($request->all(), [
            'parse_file_hash' => 'required|string',
            'info_file_hash' => 'required|string',
            'dps_amount' => 'required|numeric',
            'sets' => 'between:2,5',
            'sets.*' => 'sometimes|required|numeric|exists:sets,id',
        ], [
            'parse_file_hash.required' => 'CMX Combat screen screenshot needs to be uploaded.',
            'info_file_hash.required' => 'CMX Info screen screenshot needs to be uploaded.',
            'dps_amount.required' => 'DPS Number is required.',
            'sets.max' => 'Number of sets worn during Parse should be between 2 and 5',
            'sets.*.required' => 'Select sets worn during the parse.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $dpsParse = new DpsParse();
        $dpsParse->user_id = app('auth.driver')->id();
        $dpsParse->character_id = $characterId;
        $dpsParse->dps_amount = $request->get('dps_amount');
        $dpsParse->parse_file_hash = $request->get('parse_file_hash');
        $dpsParse->info_file_hash = $request->get('info_file_hash');
        $dpsParse->sets = implode(',', $request->get('sets'));
        $dpsParse->save();

        Event::dispatch(new DpsParseSubmitted($dpsParse));
        Cache::has('character-' . $dpsParse->character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $dpsParse->character->id), JsonResponse::HTTP_CREATED);
    }

    /**
     * @param int $characterId
     * @param int $parseId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(int $characterId, int $parseId): JsonResponse
    {
        $this->authorize('user', User::class);
        $dpsParse = DpsParse::query()
            ->whereUserId(app('auth.driver')->id())
            ->whereCharacterId($characterId)
            ->whereNull('processed_by')
            ->whereId($parseId)
            ->first();
        if (!$dpsParse) {
            throw new ModelNotFoundException('Parse not found (or already processed)!');
        }
        $dpsParse->delete();
        Event::dispatch(new DpsParseDeleted($dpsParse));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

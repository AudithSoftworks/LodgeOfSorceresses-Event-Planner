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
            'superstar_file_hash' => 'required|string',
            'dps_amount' => 'required|numeric',
            'sets.*' => 'sometimes|required|numeric|exists:sets,id',
        ], [
            'parse_file_hash.required' => 'Parse screenshot needs to be uploaded.',
            'superstar_file_hash.required' => 'Superstar screenshot needs to be uploaded.',
            'dps_amount.required' => 'DPS Number is required.',
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
        $dpsParse->superstar_file_hash = $request->get('superstar_file_hash');
        $dpsParse->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $dpsParse->save();

        app('events')->dispatch(new DpsParseSubmitted($dpsParse));

        return response()->json(['lastInsertId' => $dpsParse->id], JsonResponse::HTTP_CREATED);
    }

    /**
     * @param int $characterId
     * @param int $parseId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $characterId, int $parseId): JsonResponse
    {
        $this->authorize('user', User::class);
        $dpsParse = DpsParse::query()
            ->whereUserId(app('auth.driver')->id())
            ->whereCharacterId($characterId)
            ->whereNull('processed_by')
            ->whereId($parseId)
            ->first();
        if (!$dpsParse) {
            throw new ModelNotFoundException('Parse Not Found!');
        }

        $dpsParse->sets = $this->parseDpsParseSets($dpsParse);
        $this->parseScreenshotFiles($dpsParse);

        return response()->json($dpsParse);
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
        app('events')->dispatch(new DpsParseDeleted($dpsParse));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

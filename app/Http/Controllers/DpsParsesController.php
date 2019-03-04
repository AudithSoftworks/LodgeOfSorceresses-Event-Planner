<?php

namespace App\Http\Controllers;

use App\Events\DpsParses\DpsParseDeleted;
use App\Events\DpsParses\DpsParseSubmitted;
use App\Models\DpsParse;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DpsParsesController extends Controller
{
    /**
     * @param int $char
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $char)
    {
        $dpsParses = DpsParse::query()
            ->whereUserId(app('auth.driver')->id())
            ->whereCharacterId($char)
            ->orderBy('id', 'desc')
            ->get();
        if ($dpsParses->count()) {
            app('cache.store')->has('equipmentSets'); // Trigger Recache listener.
            $equipmentSets = app('cache.store')->get('equipmentSets');
            foreach ($dpsParses as $dpsParse) {
                $characterEquipmentSets = array_filter($equipmentSets, function ($key) use ($dpsParse) {
                    return in_array($key, explode(',', $dpsParse->sets));
                }, ARRAY_FILTER_USE_KEY);
                $dpsParse->sets = array_values($characterEquipmentSets);
                $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
                $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
                $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
                $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
            }
        }

        return response()->json(['dpsParses' => $dpsParses]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $char
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function store(Request $request, int $char): JsonResponse
    {
        $validatorErrorMessages = [
            'parse_file_hash.required' => 'Parse screenshot needs to be uploaded.',
            'superstar_file_hash.required' => 'Superstar screenshot needs to be uploaded.',
            'dps_amount.required' => 'DPS Number is required.',
            'sets.*.required' => 'Select sets worn during the parse.',
        ];
        $validator = app('validator')->make($request->all(), [
            'parse_file_hash' => 'required|string',
            'superstar_file_hash' => 'required|string',
            'dps_amount' => 'required|numeric',
            'sets.*' => 'sometimes|required|numeric|exists:equipment_sets,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $dpsParse = new DpsParse();
        $dpsParse->user_id = app('auth.driver')->id();
        $dpsParse->character_id = $char;
        $dpsParse->dps_amount = $request->get('dps_amount');
        $dpsParse->parse_file_hash = $request->get('parse_file_hash');
        $dpsParse->superstar_file_hash = $request->get('superstar_file_hash');
        $dpsParse->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $dpsParse->save();

        app('events')->dispatch(new DpsParseSubmitted($dpsParse));

        return response()->json([], JsonResponse::HTTP_CREATED);
    }

    /**
     * @param int $char
     * @param int $parse
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $char, int $parse): JsonResponse
    {
        $dpsParse = DpsParse::whereUserId(app('auth.driver')->id())->whereCharacterId($char)->whereId($parse)->get();
        $dpsParse->parse_file_hash = File::whereHash($dpsParse->parse_file_hash)->get();
        $dpsParse->superstar_file_hash = File::whereHash($dpsParse->superstar_file_hash)->get();

        return response()->json($dpsParse);
    }

    /**
     * @param int $char
     * @param int $parse
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(int $char, int $parse): JsonResponse
    {
        $dpsParse = DpsParse::whereUserId(app('auth.driver')->id())->whereCharacterId($char)->whereId($parse);
        $dpsParse->delete();
        app('events')->dispatch(new DpsParseDeleted($dpsParse->withTrashed()->first()));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

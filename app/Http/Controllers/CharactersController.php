<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\File;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class CharactersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('view', Character::class);
        $characters = Character::query()
            ->with(['dpsParses' => static function (HasMany $query) {
                $query->whereNull('processed_by');
            }])
            ->whereUserId(app('auth.driver')->id())
            ->orderBy('id', 'desc')
            ->get();
        if ($characters->count()) {
            app('cache.store')->has('sets'); // Trigger Recache listener.
            $sets = app('cache.store')->get('sets');
            foreach ($characters as $character) {
                $character->class = ClassTypes::getClassName($character->class);
                $character->role = RoleTypes::getRoleName($character->role);
                $characterEquipmentSets = array_filter($sets, static function ($key) use ($character) {
                    return in_array($key, explode(',', $character->sets), false);
                }, ARRAY_FILTER_USE_KEY);
                $character->sets = array_values($characterEquipmentSets);

                foreach ($character->dpsParses as $dpsParse) {
                    $setsUsedInDpsParse = array_filter($sets, static function ($key) use ($dpsParse) {
                        return in_array($key, explode(',', $dpsParse->sets), false);
                    }, ARRAY_FILTER_USE_KEY);
                    $dpsParse->sets = array_values($setsUsedInDpsParse);

                    $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
                    $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
                    if (!$parseFile || !$superstarFile) {
                        throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
                    }
                    $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
                    $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
                }
            }
        }

        return response()->json($characters);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Character::class);
        $validatorErrorMessages = [
            'name.required' => 'Character name is required.',
            'role.required' => 'Choose a role.',
            'class.required' => 'Choose a class.',
            'sets.*.required' => 'Select all full sets your character has.',
        ];
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|string',
            'role' => 'required|integer|min:1|max:4',
            'class' => 'required|integer|min:1|max:6',
            'sets.*' => 'required|numeric|exists:sets,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $character = new Character();
        $character->user_id = app('auth.driver')->id();
        $character->name = $request->get('name');
        $character->role = $request->get('role');
        $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->save();

        return response()->json(['lastInsertId' =>  $character->id], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $char
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $char): JsonResponse
    {
        $this->authorize('view', Character::class);
        $character = Character::query()
            ->with(['dpsParses' => static function (HasMany $query) {
                $query->whereNull('processed_by');
            }])
            ->whereUserId(app('auth.driver')->id())
            ->whereId($char)
            ->first(['id', 'name', 'class', 'role', 'sets', 'last_submitted_dps_amount']);
        if (!$character) {
            return response()->json(['message' => 'Character not found!'])->setStatusCode(404);
        }

        $character->class = ClassTypes::getClassName($character->class);
        $character->role = RoleTypes::getRoleName($character->role);

        app('cache.store')->has('sets'); // Trigger Recache listener.
        $sets = app('cache.store')->get('sets');
        $charactersSets = array_filter($sets, static function ($key) use ($character) {
            return in_array($key, explode(',', $character->sets), false);
        }, ARRAY_FILTER_USE_KEY);
        $character->sets = array_values($charactersSets);

        foreach ($character->dpsParses as $dpsParse) {
            $setsUsedInDpsParse = array_filter($sets, static function ($key) use ($dpsParse) {
                return in_array($key, explode(',', $dpsParse->sets), false);
            }, ARRAY_FILTER_USE_KEY);
            $dpsParse->sets = array_values($setsUsedInDpsParse);

            $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
            $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
            if (!$parseFile || !$superstarFile) {
                throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
            }
            $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
            $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
        }

        return response()->json($character);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $char
     *
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, int $char): JsonResponse
    {
        $this->authorize('update', Character::class);
        $validatorErrorMessages = [
            'name.required' => 'Character name can\'t be empty.',
            'role.required' => 'Choose a role.',
            'class.required' => 'Choose a class.',
            'sets.*.required' => 'Select all full sets your character has.',
        ];
        $validator = app('validator')->make($request->all(), [
            'name' => 'sometimes|required|string',
            'role' => 'sometimes|required|integer|min:1|max:4',
            'class' => 'sometimes|required|integer|min:1|max:6',
            'sets.*' => 'required|numeric|exists:sets,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $character = Character::query()->where(static function (Builder $query) use ($char) {
            $query
                ->where('user_id', app('auth.driver')->id())
                ->where('id', $char);
        })->first(['id', 'name', 'class', 'role', 'sets']);
        if (!$character) {
            return response()->json(['message' => 'Character not found!'])->setStatusCode(404);
        }
        $character->user_id = app('auth.driver')->id();
        $request->filled('name') && $character->name = $request->get('name');
        $request->filled('role') && $character->role = $request->get('role');
        $request->filled('class') && $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->save();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $char
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(int $char): JsonResponse
    {
        $this->authorize('delete', Character::class);
        $character = Character::query()
            ->whereUserId(app('auth.driver')->id())
            ->whereApprovedForMidgame(false)
            ->whereApprovedForEndgameT0(false)
            ->whereApprovedForEndgameT1(false)
            ->whereApprovedForEndgameT2(false)
            ->whereId($char)
            ->first();
        if ($character) {
            $character->delete();
        } else {
            throw new ModelNotFoundException('Character not found!');
        }

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

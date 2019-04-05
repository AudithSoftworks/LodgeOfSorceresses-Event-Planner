<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            ->where('user_id', app('auth.driver')->id())
            ->orderBy('id', 'desc')
            ->get();
        if ($characters->count()) {
            app('cache.store')->has('equipmentSets'); // Trigger Recache listener.
            $equipmentSets = app('cache.store')->get('equipmentSets');
            foreach ($characters as $character) {
                $character->class = ClassTypes::getClassName($character->class);
                $character->role = RoleTypes::getRoleName($character->role);
                $characterEquipmentSets = array_filter($equipmentSets, static function ($key) use ($character) {
                    return in_array($key, explode(',', $character->sets), false);
                }, ARRAY_FILTER_USE_KEY);
                $character->sets = array_values($characterEquipmentSets);
            }
        }

        return response()->json([
            'characters' => $characters
        ]);
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
            'sets.*.required' => 'Select sets used during the parse.',
        ];
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|string',
            'role' => 'required|integer|min:1|max:4',
            'class' => 'required|integer|min:1|max:6',
            'sets.*' => 'sometimes|required|numeric|exists:equipment_sets,id',
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

        return response()->json(['success' => true], JsonResponse::HTTP_CREATED);
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
        $character = Character::query()->where(static function (Builder $query) use ($char) {
            $query
                ->where('user_id', app('auth.driver')->id())
                ->where('id', $char);
        })->first(['id', 'name', 'class', 'role', 'sets']);
        if (!$character) {
            return response()->json(['message' => 'Character not found! Redirecting to My Characters page...'])->setStatusCode(404);
        }
        $character->sets = array_map(static function ($item) {
            return (int)$item;
        }, explode(',', $character->sets));

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
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|string',
            'role' => 'required|integer|min:1|max:4',
            'class' => 'required|integer|min:1|max:6',
            'sets.*' => 'sometimes|required|numeric|exists:equipment_sets,id',
        ]);
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
        $character->name = $request->get('name');
        $character->role = $request->get('role');
        $character->class = $request->get('class');
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
     */
    public function destroy(int $char): JsonResponse
    {
        $this->authorize('delete', Character::class);
        Character::destroy($char);

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

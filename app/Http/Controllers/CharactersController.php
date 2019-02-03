<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\EquipmentSet;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CharactersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $characters = Character::query()->where('user_id', '=', app('auth.driver')->id())->orderBy('id', 'desc')->get();
        if ($characters->count()) {
            $equipmentSets = app('cache.store')->remember('equipmentSets', EquipmentSet::CACHE_TTL, function () {
                return EquipmentSet::query()->get()->keyBy('id')->toArray();
            });
            foreach ($characters as $character) {
                $character->class = ClassTypes::getClassName($character->class);
                $character->role = RoleTypes::getRoleName($character->role);
                $characterEquipmentSets = array_filter($equipmentSets, function ($key) use ($character) {
                    return in_array($key, explode(',', $character->sets));
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
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|string',
            'role' => 'required|integer|min:1|max:4',
            'class' => 'required|integer|min:1|max:5',
            'sets.*' => 'sometimes|required|numeric|exists:equipment_sets,id',
        ]);
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

        return response()->json(['success' => true], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        Character::destroy($id);

        return response()->json(['success' => true]);
    }
}

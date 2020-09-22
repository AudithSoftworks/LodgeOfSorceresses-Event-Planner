<?php

/**
 * @noinspection PhpUnusedParameterInspection
 * @todo Remove this when upgrading to PHP-8, where we can have argument types without arguments
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CharactersRequests;
use App\Models\Character;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CharactersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\CharactersRequests $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CharactersRequests $request): JsonResponse
    {
        $characterIds = Character::query()
            ->whereUserId(Auth::id())
            ->orderBy('name')
            ->get(['id'])->pluck('id');

        $characters = collect();
        foreach ($characterIds as $characterId) {
            app('cache.store')->has('character-' . $characterId); // Trigger Recache listener.
            $character = app('cache.store')->get('character-' . $characterId);
            $characters->add($character);
        }

        return response()->json($characters);
    }

    public function store(CharactersRequests $request): JsonResponse
    {
        $character = new Character();
        $character->user_id = Auth::id();
        $character->name = $request->get('name');
        $character->role = $request->get('role');
        $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->skills = !empty($request->get('skills')) ? implode(',', $request->get('skills')) : null;
        $character->save();

        if (!empty($request->get('content'))) {
            $charactersContent = array_filter($request->get('content'), static function ($item) {
                return !empty($item);
            });
            if (!empty($charactersContent)) {
                $character->content()->sync($charactersContent);
            }
        }

        $character->save();

        Cache::has('character-' . $character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $character->id), JsonResponse::HTTP_CREATED);
    }

    public function update(CharactersRequests $request, Character $character): JsonResponse
    {
        if (!$character || $character->owner->id !== Auth::id()) {
            throw new ModelNotFoundException('Character not found!');
        }

        $request->filled('name') && $character->name = $request->get('name');
        $character->approved_for_tier === 0 && $request->filled('role') && $character->role = $request->get('role');
        $character->approved_for_tier === 0 && $request->filled('class') && $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->skills = !empty($request->get('skills')) ? implode(',', $request->get('skills')) : null;

        if (!empty($request->get('content'))) {
            $charactersContent = array_filter($request->get('content'), static function ($item) {
                return !empty($item);
            });
            $character->content()->sync($charactersContent);
        }

        $character->save();

        Cache::has('character-' . $character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $character->id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\CharactersRequests $request
     * @param \App\Models\Character $character
     *
     * @throws \Exception
     * @return JsonResponse
     */
    public function destroy(CharactersRequests $request, Character $character): JsonResponse
    {
        if (!$character || $character->approved_for_tier !== 0 || $character->owner->id !== Auth::id()) {
            throw new ModelNotFoundException('Character not found!');
        }

        $character->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

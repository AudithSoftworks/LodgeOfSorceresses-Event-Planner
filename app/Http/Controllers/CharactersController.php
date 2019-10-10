<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Traits\Characters\HasOrIsDpsParse;
use App\Traits\Characters\IsCharacter;
use Illuminate\Http\JsonResponse;

class CharactersController extends Controller
{
    use IsCharacter, HasOrIsDpsParse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('user', User::class);
        $query = Character::query();
        $characterIds = $query
            ->orderBy('id', 'desc')
            ->get(['id'])->pluck('id');

        $characters = collect();
        foreach ($characterIds as $characterId) {
            app('cache.store')->has('character-' . $characterId); // Trigger Recache listener.
            $character = app('cache.store')->get('character-' . $characterId);
            $characters->add($character);
        }

        return response()->json($characters);
    }

    /**
     * Display the specified resource.
     *
     * @param int $characterId
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $characterId): JsonResponse
    {
        $this->authorize('limited', User::class);
        app('cache.store')->has('character-' . $characterId); // Trigger Recache listener.
        $character = app('cache.store')->get('character-' . $characterId);
        if (!$character) {
            return response()->json(['message' => 'Character not found!'])->setStatusCode(404);
        }

        return response()->json($character);
    }
}

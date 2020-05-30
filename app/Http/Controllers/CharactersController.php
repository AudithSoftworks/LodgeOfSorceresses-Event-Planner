<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Traits\Character\HasOrIsDpsParse;
use App\Traits\Character\IsCharacter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CharactersController extends Controller
{
    use IsCharacter, HasOrIsDpsParse;

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('user', User::class);

        $query = Character::query();
        if ($request->has('tier') && is_numeric($tier = $request->get('tier'))) {
            $query->where('approved_for_tier', '>=', (int)$tier);
        }
        $characterIds = $query->orderBy('user_id')->orderBy('name')->get(['id'])->pluck('id');

        $characters = collect();
        foreach ($characterIds as $characterId) {
            Cache::has('character-' . $characterId); // Trigger Recache listener.
            $character = Cache::get('character-' . $characterId);
            $character !== null && $characters->add($character);
        }

        return response()->json($characters);
    }

    /**
     * @param int $characterId
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $characterId): JsonResponse
    {
        $this->authorize('limited', User::class);

        Cache::has('character-' . $characterId); // Trigger Recache listener.
        $character = Cache::get('character-' . $characterId);
        if (!$character) {
            return response()->json(['message' => 'Character not found!'])->setStatusCode(404);
        }

        return response()->json($character);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Events\Character\CharacterDemoted;
use App\Events\Character\CharacterPromoted;
use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\User;
use App\Singleton\RoleTypes;
use App\Traits\Character\HasOrIsDpsParse;
use App\Traits\Character\IsCharacter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $characterIds = Character::query()
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
     * @param \Illuminate\Http\Request $request
     * @param int                      $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, int $characterId): JsonResponse
    {
        $this->authorize('admin', User::class);

        $validatorErrorMessages = [
            'action.required' => 'Action should either be promote or demote.',
        ];
        $validator = app('validator')->make($request->all(), [
            'action' => 'required|string|in:promote,demote',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!($character = Character::query()->with('owner')->find($characterId))) {
            throw new ModelNotFoundException('Character not found!');
        }

        $user = $character->owner;
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        if ($me->id === $user->id) {
            throw new AuthorizationException('Self-ranking disabled!');
        }

        if ($character->role === RoleTypes::ROLE_MAGICKA_DD || $character->role === RoleTypes::ROLE_STAMINA_DD) {
            throw new \InvalidArgumentException('Damage Dealers can only be ranked via Parse submission!');
        }

        $guildRankingService = app('guild.ranks.clearance');
        switch ($actionParam = $request->get('action')) {
            case 'promote':
                $guildRankingService->promoteCharacter($character);
                app('events')->dispatch(new CharacterPromoted($character));
                break;
            case 'demote':
                $guildRankingService->demoteCharacter($character);
                app('events')->dispatch(new CharacterDemoted($character));
                break;
        }

        return response()->json(['message' => 'Character reranked.']);
    }
}

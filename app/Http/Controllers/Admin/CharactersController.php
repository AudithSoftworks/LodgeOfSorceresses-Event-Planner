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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CharactersController extends Controller
{
    use IsCharacter, HasOrIsDpsParse;

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, int $characterId): JsonResponse
    {
        $this->authorize('admin', User::class);

        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:promote,demote',
        ], [
            'action.required' => 'Action is required.',
            'action.in' => 'Action should either be promote or demote.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!($character = Character::query()->with('owner')->find($characterId))) {
            throw new ModelNotFoundException('Character not found!');
        }

        $user = $character->owner;
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if ($me->id === $user->id) {
            throw new AuthorizationException('Self-ranking disabled!');
        }

        if ($character->role === RoleTypes::ROLE_MAGICKA_DD || $character->role === RoleTypes::ROLE_STAMINA_DD) {
            $validator->errors()->add('action', 'Damage Dealers can only be ranked via Parse submission!');
            throw new ValidationException($validator);
        }

        $guildRankingService = app('guild.ranks.clearance');
        switch ($actionParam = $request->get('action')) {
            case 'promote':
                $guildRankingService->promoteCharacter($character);
                Event::dispatch(new CharacterPromoted($character));
                break;
            case 'demote':
                $guildRankingService->demoteCharacter($character);
                Event::dispatch(new CharacterDemoted($character));
                break;
        }
        Cache::has('character-' . $character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $character->id));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Events\Character\CharacterDemoted;
use App\Events\Character\CharacterPromoted;
use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\DpsParse;
use App\Models\File;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class CharactersController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $char
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, int $char): JsonResponse
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

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        if (!$me->id === $char) {
            throw new AuthorizationException('Self-ranking disabled!');
        }

        if (!($character = Character::query()->find($char))) {
            throw new ModelNotFoundException('Character not found!');
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

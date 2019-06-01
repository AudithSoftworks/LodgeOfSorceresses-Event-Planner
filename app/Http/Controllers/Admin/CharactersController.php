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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('admin', User::class);
        $characters = Character::query()
            ->with(['owner'])
            ->where('user_id', '!=', app('auth.driver')->id())
            ->orderBy('id', 'desc')
            ->get();
        if ($characters->count()) {
            app('cache.store')->has('sets'); // Trigger Recache listener.
            $equipmentSets = app('cache.store')->get('sets');
            foreach ($characters as $character) {
                $character->class = ClassTypes::getClassName($character->class);
                $character->role = RoleTypes::getShortRoleText($character->role);
                $characterEquipmentSets = array_filter($equipmentSets, static function ($key) use ($character) {
                    return in_array($key, explode(',', $character->sets), false);
                }, ARRAY_FILTER_USE_KEY);
                $character->sets = array_values($characterEquipmentSets);
            }
        }

        return response()->json($characters);
    }

    /**
     * @param int $char
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $char): JsonResponse
    {
        $this->authorize('admin', User::class);
        $dpsParses = DpsParse::query()
            ->with(['owner', 'character'])
            ->whereHas('owner', static function (Builder $queryToGetOauthAccounts) {
                $queryToGetOauthAccounts->whereHas('linkedAccounts', static function (Builder $queryToGetDiscordOauthAccounts) {
                    $queryToGetDiscordOauthAccounts->where('remote_provider', '=', 'discord');
                });
            })
            ->whereNotNull('processed_by')
            ->whereCharacterId($char)
            ->orderBy('id', 'desc')
            ->get();
        if ($dpsParses->count()) {
            app('cache.store')->has('sets'); // Trigger Recache listener.
            $sets = app('cache.store')->get('sets');
            foreach ($dpsParses as $dpsParse) {
                $characterSets = array_filter($sets, static function ($key) use ($dpsParse) {
                    return in_array($key, explode(',', $dpsParse->sets), false);
                }, ARRAY_FILTER_USE_KEY);
                $dpsParse->sets = array_values($characterSets);
                is_int($dpsParse->character->role) && $dpsParse->character->role = RoleTypes::getShortRoleText($dpsParse->character->role);
                is_int($dpsParse->character->class) && $dpsParse->character->class = ClassTypes::getClassName($dpsParse->character->class);
                $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
                $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
                if (!$parseFile || !$superstarFile) {
                    throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
                }
                $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
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

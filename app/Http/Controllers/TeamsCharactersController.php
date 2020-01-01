<?php

namespace App\Http\Controllers;

use App\Events\Team\TeamUpdated;
use App\Models\Character;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeamsCharactersController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $teamId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, int $teamId): JsonResponse
    {
        $this->authorize('user', User::class);

        $validatorErrorMessages = [
            'characterIds.*.required' => 'Provide character(s) to add to the team.',
        ];
        $validator = Validator::make($request->all(), [
            'characterIds.*' => 'required|numeric|exists:characters,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $myId = Auth::id();
        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var Team $team */
        $team = Cache::get('team-' . $teamId);

        if ($team->led_by !== $myId && $team->created_by !== $myId) {
            throw new AuthorizationException('Only team leader or creator can invite new members!');
        }

        $characterIds = array_unique($request->get('characterIds'));
        $characters = collect();
        foreach ($characterIds as $characterId) {
            Cache::has('character-' . $characterId); // Recache trigger.
            $character = Cache::get('character-' . $characterId);
            $character && $characters->add($character);
        }
        $eligibleCharacters = $characters->filter(static function (Character $character) use ($team) {
            return $character->approved_for_tier >= $team->tier;
        });

        $teamMemberIds = $team->members->pluck('id')
            ->merge($eligibleCharacters->pluck('id'))
            ->unique();
        $team->members()->sync($teamMemberIds);
        $team->save();

        Event::dispatch(new TeamUpdated($team));

        return response()->json($team, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param int $teamId
     * @param int $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $teamId, int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);

        Cache::has('team-' . $teamId); // Trigger Recache listener.
        /** @var \App\Models\Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            return response()->json(['message' => 'Team not found!'])->setStatusCode(404);
        }
        $pivot = $team->members->filter(static function (Character $character) use ($characterId) {
            return $character->id === $characterId;
        })->first()->teamMembership;
        if (!$pivot) {
            return response()->json(['message' => 'Team membership record not found!'])->setStatusCode(404);
        }

        return response()->json($pivot, JsonResponse::HTTP_OK);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $teamId
     * @param int                      $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, int $teamId, int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);

        $validatorErrorMessages = [
            'accepted_terms' => 'Please make sure you accept the terms of membership.',
        ];
        $validator = Validator::make($request->all(), [
            'accepted_terms' => 'required|accepted',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $myId = Auth::id();
        Cache::has('character-' . $characterId); // Recache trigger.
        /** @var Character $character */
        $character = Cache::get('character-' . $characterId);
        $character->loadMissing(['owner']);
        if ($character->owner->id !== $myId) {
            throw new AuthorizationException('You can\'t manage someone else\'s team membership options!');
        }

        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var Team $team */
        $team = Cache::get('team-' . $teamId);

        $team->members()->where('character_id', '=', $characterId)->update([
            'status' => true,
            'accepted_terms' => true,
        ]);

        Event::dispatch(new TeamUpdated($team));

        return response()->json($team, JsonResponse::HTTP_OK);
    }

    public function destroy($id): JsonResponse
    {
        //
    }
}

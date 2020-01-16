<?php

namespace App\Http\Controllers;

use App\Events\Team\MemberInvited;
use App\Events\Team\MemberJoined;
use App\Events\Team\MemberRemoved;
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
     * Endpoint to list team membership records for a given team.
     *
     * @param int $teamId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(int $teamId): JsonResponse
    {
        $this->authorize('user', User::class);

        Cache::has('team-' . $teamId); // Trigger Recache listener.
        /** @var \App\Models\Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            return response()->json(['message' => 'Team not found!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }
        $teamMembersFiltered = $team->members->filter(static function (Character $character) {
            /** @noinspection PhpUndefinedFieldInspection */
            return $character->teamMembership->status;
        });

        return response()->json($teamMembersFiltered, JsonResponse::HTTP_OK);
    }

    /**
     * Endpoint to invite a team member.
     *
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

        $validator = Validator::make($request->all(), [
            'characterIds' => 'required|array',
            'characterIds.*' => 'required|numeric|exists:characters,id',
        ], [
            'characterIds.required' => 'Select the character(s) to be added to the team.',
            'characterIds.*.exists' => 'No such characters exist.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $myId = Auth::id();
        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            return response()->json(['message' => 'Team not found!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }
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

        foreach ($teamMemberIds as $characterId) {
            Event::dispatch(new MemberInvited(Cache::get('character-' . $characterId), $team));
        }
        Event::dispatch(new TeamUpdated($team));
        Cache::has('team-' . $team->id); // Recache trigger.

        return response()->json(Cache::get('team-' . $team->id), JsonResponse::HTTP_CREATED);
    }

    /**
     * Endpoint to show a team membership record.
     *
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
            return response()->json(['message' => 'Team not found!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }
        $teamMembersFiltered = $team->members->filter(static function (Character $character) use ($characterId) {
            return $character->id === $characterId;
        });
        if (!$teamMembersFiltered->count()) {
            return response()->json(['message' => 'Team has no such member!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $teamMembersFiltered->first()->teamMembership;

        return response()->json($pivot, JsonResponse::HTTP_OK);
    }

    /**
     * Endpoint for team member to update their membership record.
     *
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
            'accepted_terms.accepted' => 'Please make sure you accept the terms of membership.',
        ];
        $validator = Validator::make($request->all(), [
            'accepted_terms' => 'required|accepted',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            return response()->json(['message' => 'Team not found!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }
        $teamMembersFiltered = $team->members->filter(static function (Character $character) use ($characterId) {
            return $character->id === $characterId;
        });
        if (!$teamMembersFiltered->count()) {
            return response()->json(['message' => 'Team has no such member!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var Character $character */
        $character = $teamMembersFiltered->first();
        $character->loadMissing('owner');
        $myId = Auth::id();
        if ($character->owner->id !== $myId) {
            throw new AuthorizationException('You can\'t manage someone else\'s team membership options!');
        }

        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        /** @noinspection PhpUndefinedFieldInspection */
        $pivot = $character->teamMembership;
        /** @noinspection PhpUndefinedFieldInspection */
        $pivot->status = $pivot->accepted_terms = true;
        $pivot->save();

        Event::dispatch(new MemberJoined($character, $team));
        Event::dispatch(new TeamUpdated($team));
        Cache::has('team-' . $team->id); // Recache trigger.

        return response()->json(Cache::get('team-' . $team->id), JsonResponse::HTTP_OK);
    }

    /**
     * Endpoint to remove a team member.
     *
     * @param int $teamId
     * @param int $characterId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $teamId, int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);

        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            return response()->json(['message' => 'Team not found!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        $teamMembersFiltered = $team->members->filter(static function (Character $character) use ($characterId) {
            return $character->id === $characterId;
        });
        if (!$teamMembersFiltered->count()) {
            return response()->json(['message' => 'Team has no such member!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        $myId = Auth::id();
        /** @var Character $character */
        $character = $teamMembersFiltered->first();
        if ($character->owner->id !== $myId && $team->led_by !== $myId && $team->created_by !== $myId) {
            throw new AuthorizationException('Not allowed to terminate this team membership record! Only the member themselves or the team leader can do that.');
        }

        /** @noinspection PhpUndefinedFieldInspection */
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $character->teamMembership;
        $isMemberActive = (bool)$pivot->status;
        $pivot->delete();

        $isMemberActive && Event::dispatch(new MemberRemoved($character, $team));
        Event::dispatch(new TeamUpdated($team));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

<?php

/**
 * @noinspection PhpUnusedParameterInspection
 * @todo         Remove this when upgrading to PHP-8, where we can have argument types without arguments
 */

namespace App\Http\Controllers;

use App\Events\Team\MemberInvited;
use App\Events\Team\MemberJoined;
use App\Events\Team\MemberRemoved;
use App\Events\Team\TeamUpdated;
use App\Http\Requests\TeamsCharactersRequests;
use App\Models\Character;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class TeamsCharactersController extends Controller
{
    /**
     * Endpoint to list team membership records for a given team.
     *
     * @param int $teamId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
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
     * @param \App\Http\Requests\TeamsCharactersRequests $request
     * @param \App\Models\Team $team
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TeamsCharactersRequests $request, Team $team): JsonResponse
    {
        $myId = Auth::id();
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
        $existingMembersIds = $team->members->pluck('id');
        $eligibleCharacters = $characters->reject(static function (Character $c) use ($team, $existingMembersIds) {
            return $c->approved_for_tier < $team->tier || $existingMembersIds->contains($c->id);
        });
        $eligibleCharacterIds = $eligibleCharacters->pluck('id');
        $teamMemberIds = $existingMembersIds->merge($eligibleCharacterIds)->unique();
        $team->members()->sync($teamMemberIds);
        $team->save();

        foreach ($eligibleCharacterIds as $characterId) {
            Event::dispatch(new MemberInvited(Cache::get('character-' . $characterId), $team));
        }
        Event::dispatch(new TeamUpdated($team));
        Cache::has('team-' . $team->id); // Recache trigger.

        return response()->json(Cache::get('team-' . $team->id), JsonResponse::HTTP_CREATED);
    }

    /**
     * Endpoint to show a team membership record.
     *
     * @param \App\Http\Requests\TeamsCharactersRequests $request
     * @param \App\Models\Team $team
     * @param \App\Models\Character $character
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TeamsCharactersRequests $request, Team $team, Character $character): JsonResponse
    {
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $character->teams->first()->teamMembership;

        return response()->json($pivot, JsonResponse::HTTP_OK);
    }

    /**
     * Endpoint for team member to update their membership record.
     *
     * @param \App\Http\Requests\TeamsCharactersRequests $request
     * @param \App\Models\Team $team
     * @param \App\Models\Character $character
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TeamsCharactersRequests $request, Team $team, Character $character): JsonResponse
    {
        $character->loadMissing('owner');
        if ($character->owner->id !== Auth::id()) {
            throw new AuthorizationException('This team invitation doesn\'t belong to you!');
        }

        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $character->teams->first()->teamMembership;
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
     * @param \App\Http\Requests\TeamsCharactersRequests $request
     * @param \App\Models\Team $team
     * @param \App\Models\Character $character
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TeamsCharactersRequests $request, Team $team, Character $character): JsonResponse
    {
        $myId = Auth::id();
        if ($character->owner->id !== $myId && $team->led_by !== $myId && $team->created_by !== $myId) {
            throw new AuthorizationException('Not allowed to terminate this team membership record! Only the member themselves or the team leader can do that.');
        }

        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $character->teams->first()->teamMembership;
        /** @noinspection PhpUndefinedFieldInspection */
        $isMemberActive = (bool)$pivot->status;
        $pivot->delete();

        $isMemberActive && Event::dispatch(new MemberRemoved($character, $team));
        Event::dispatch(new TeamUpdated($team));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

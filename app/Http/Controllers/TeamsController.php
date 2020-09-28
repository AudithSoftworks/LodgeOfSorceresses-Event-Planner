<?php

/**
 * @noinspection PhpUnusedParameterInspection
 * @todo Remove this when upgrading to PHP-8, where we can have argument types without arguments
 */

namespace App\Http\Controllers;

use App\Http\Requests\TeamsRequests;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TeamsController extends Controller
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $this->authorize('user', User::class);

        $teamIds = Team::query()
            ->orderBy('id', 'desc')
            ->get(['id'])->pluck('id');

        $teams = collect();
        foreach ($teamIds as $teamId) {
            Cache::has('team-' . $teamId); // Trigger Recache listener.
            $team = Cache::get('team-' . $teamId);
            $teams->add($team);
        }

        return response()->json($teams);
    }

    /**
     * @param \App\Http\Requests\TeamsRequests $request
     *
     * @throws \Illuminate\Validation\ValidationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TeamsRequests $request): JsonResponse
    {
        $validator = $request->getValidator();

        $tierParam = $request->get('tier');

        $team = new Team();
        $team->name = $request->get('name');
        $team->tier = $tierParam;
        $team->discord_role_id = $request->get('discord_role_id');
        $request->has('discord_lobby_channel_id') && $team->discord_lobby_channel_id = $request->get('discord_lobby_channel_id');
        $request->has('discord_rant_channel_id') && $team->discord_rant_channel_id = $request->get('discord_rant_channel_id');
        $team->created_by = Auth::id();

        $ledByParam = $request->get('led_by');
        Cache::has('user-' . $ledByParam); // Recache trigger.
        /** @var \App\Models\User $ledBy */
        $ledBy = Cache::get('user-' . $ledByParam);
        $policyResponseForCanJoin = Gate::forUser($ledBy)->inspect('canJoin', $team);
        if ($policyResponseForCanJoin->denied()) {
            $validator->errors()->add('led_by', $policyResponseForCanJoin->message());
            throw new ValidationException($validator);
        }
        $team->led_by = $ledBy->id;

        $team->save();
        Cache::has('team-' . $team->id); // Recache trigger.

        return response()->json(Cache::get('team-' . $team->id), JsonResponse::HTTP_CREATED);
    }

    /**
     * @param int $teamId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $teamId): JsonResponse
    {
        $this->authorize('user', User::class);

        Cache::has('team-' . $teamId); // Trigger Recache listener.
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            return response()->json(['message' => 'Team not found!'])->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json($team);
    }

    /**
     * @param \App\Http\Requests\TeamsRequests $request
     * @param int $teamId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TeamsRequests $request, int $teamId): JsonResponse
    {
        $validator = $request->getValidator();

        $myId = Auth::id();
        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var \App\Models\Team $team */
        $team = Cache::get('team-' . $teamId);

        if (!$team) {
            throw new ModelNotFoundException('Team not found!');
        }
        if ($team->led_by !== $myId && $team->created_by !== $myId && Gate::denies('is-admin')) {
            throw new AuthorizationException();
        }

        $team->name = $request->get('name');
        $team->discord_role_id = $request->get('discord_role_id');
        $request->has('discord_lobby_channel_id') && $team->discord_lobby_channel_id = $request->get('discord_lobby_channel_id');
        $request->has('discord_rant_channel_id') && $team->discord_rant_channel_id = $request->get('discord_rant_channel_id');

        if ($request->has('tier') && ($tierParam = (int)$request->get('tier')) !== $team->tier) {
            if (!app('teams.eligibility')->areAllMembersOfTeamEligibleForPossibleNewTeamTier($team, $tierParam)) { // Team tier increase needs handling.
                $validator->errors()->add('tier', 'Requested Tier is too high for some members of this team! Consider removing these members before increasing Tier.');
                throw new ValidationException($validator);
            }
            $team->tier = $tierParam;
        }

        if ($request->has('led_by') && ($ledByParam = (int)$request->get('led_by')) !== $team->led_by) {
            Cache::has('user-' . $ledByParam); // Recache trigger.
            /** @var \App\Models\User $ledBy */
            $ledBy = Cache::get('user-' . $ledByParam);
            $policyResponseForCanJoin = Gate::forUser($ledBy)->inspect('canJoin', $team);
            if ($policyResponseForCanJoin->denied()) {
                $validator->errors()->add('led_by', $policyResponseForCanJoin->message());
                throw new ValidationException($validator);
            }
            $team->led_by = $ledByParam;
        }

        $team->save();
        Cache::has('team-' . $team->id); // Recache trigger.

        return response()->json(Cache::get('team-' . $team->id), JsonResponse::HTTP_OK);
    }

    /**
     * @param \App\Http\Requests\TeamsRequests $request
     * @param \App\Models\Team $team
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TeamsRequests $request, Team $team): JsonResponse
    {
        $myId = Auth::id();
        if ($team->led_by !== $myId && $team->created_by !== $myId && Gate::denies('is-admin')) {
            throw new AuthorizationException();
        }

        $team->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

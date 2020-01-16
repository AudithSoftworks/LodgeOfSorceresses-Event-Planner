<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeamsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('admin', User::class);

        $discordRoleIds = collect(app('discord.api')->getGuildRoles())
            ->reject(static function ($item) {
                return $item['hoist'] === true || $item['mentionable'] === false;
            })->implode('id', ',');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'tier' => 'required|integer|between:1,4',
            'discord_id' => 'required|string|numeric|in:' . $discordRoleIds,
            'led_by' => 'required|numeric|exists:users,id',
        ], [
            'name.required' => 'Team name is required.',
            'tier.required' => 'Choose a tier for the content this team is specifialized in.',
            'tier.between' => 'Tier must be from 1 to 4.',
            'discord_id.required' => 'Discord Role-ID is required.',
            'discord_id.in' => 'Discord Role-ID isn\'t valid.',
            'led_by.required' => 'Choose a team leader.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $tierParam = $request->get('tier');

        $team = new Team();
        $team->name = $request->get('name');
        $team->tier = $tierParam;
        $team->discord_id = $request->get('discord_id');
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
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @param \Illuminate\Http\Request $request
     * @param int                      $teamId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, int $teamId): JsonResponse
    {
        $this->authorize('user', User::class);

        $myId = Auth::id();
        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var \App\Models\Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            throw new ModelNotFoundException('Team not found!');
        }
        if ($team->led_by !== $myId && $team->created_by !== $myId && Gate::denies('is-admin')) {
            throw new ModelNotFoundException('Team not found!');
        }

        $discordRoleIds = collect(app('discord.api')->getGuildRoles())
            ->reject(static function ($item) {
                return $item['hoist'] === true || $item['mentionable'] === false;
            })
            ->implode('id', ',');
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'tier' => 'sometimes|required|integer|between:1,4',
            'discord_id' => 'sometimes|required|string|numeric|in:' . $discordRoleIds,
            'led_by' => 'sometimes|required|numeric|exists:users,id',
        ], [
            'name.required' => 'Team name is required.',
            'tier.required' => 'Choose a tier for the content this team is specifialized in.',
            'tier.between' => 'Tier must be from 1 to 4.',
            'discord_id.required' => 'Discord Role-ID is required.',
            'discord_id.in' => 'Discord Role-ID isn\'t valid.',
            'led_by.required' => 'Choose a team leader.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $request->has('name') && $team->name = $request->get('name');
        if ($request->has('tier')) {
            if (!app('teams.eligibility')->areAllMembersOfTeamEligibleForPossibleNewTeamTier($team, $request->get('tier'))) { // Team tier increase needs handling.
                $validator->errors()->add('tier', 'Requested Tier is too high for some members of this team! Consider removing these members before increasing Tier.');
                throw new ValidationException($validator);
            }
            $team->tier = $request->get('tier');
        }
        $request->has('discord_id') && $team->discord_id = $request->get('discord_id');

        if ($request->has('discord_id')) {
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
        }

        $team->save();
        Cache::has('team-' . $team->id); // Recache trigger.

        return response()->json(Cache::get('team-' . $team->id), JsonResponse::HTTP_OK);
    }

    /**
     * @param int $teamId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(int $teamId): JsonResponse
    {
        $this->authorize('user', User::class);

        $myId = Auth::id();
        Cache::has('team-' . $teamId); // Recache trigger.
        /** @var \App\Models\Team $team */
        $team = Cache::get('team-' . $teamId);
        if (!$team) {
            throw new ModelNotFoundException('Team not found!');
        }
        if ($team->led_by !== $myId && $team->created_by !== $myId && Gate::denies('is-admin')) {
            throw new ModelNotFoundException('Team not found!');
        }

        $team->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

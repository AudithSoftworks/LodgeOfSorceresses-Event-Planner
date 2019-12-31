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
        $validatorErrorMessages = [
            'name.required' => 'Team name is required.',
            'tier.required' => 'Choose a tier for the content this team is specifialized in.',
            'tier.between' => 'Tier must be from 1 to 4.',
            'discord_id.required' => 'Discord Role-ID is required.',
            'led_by.required' => 'Choose a team leader.',
        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'tier' => 'required|integer|between:1,4',
            'discord_id' => 'required|string|numeric',
            'led_by' => 'nullable|numeric|exists:users,id',
        ], $validatorErrorMessages);
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

        return response()->json($team, JsonResponse::HTTP_CREATED);
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
            return response()->json(['message' => 'Team not found!'])->setStatusCode(404);
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

        $validatorErrorMessages = [
            'name.required' => 'Team name is required.',
            'tier.required' => 'Choose a tier for the content this team is specifialized in.',
            'tier.between' => 'Tier must be from 1 to 4.',
            'discord_id.required' => 'Discord Role-ID is required.',
            'led_by.required' => 'Choose a team leader.',
        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'tier' => 'required|integer|between:1,4',
            'discord_id' => 'required|string|numeric',
            'led_by' => 'nullable|numeric|exists:users,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $team->name = $request->get('name');
        $team->tier = $request->get('tier');
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

        return response()->json($team, JsonResponse::HTTP_OK);
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

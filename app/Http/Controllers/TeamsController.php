<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
            app('cache.store')->has('team-' . $teamId); // Trigger Recache listener.
            $team = app('cache.store')->get('team-' . $teamId);
            $teams->add($team);
        }

        return response()->json($teams);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|string',
            'tier' => 'required|integer|between:1,4',
            'discord_id' => 'required|string|numeric',
            'led_by' => 'nullable|numeric|exists:users,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $tier = $request->get('tier');
        $led_by = $request->get('led_by');

        $listOfEligibleCharactersForGivenTier = app('teams.eligibility')->getListOfEligibleCharactersForGivenTier($led_by, $tier);
        if (!$listOfEligibleCharactersForGivenTier->count()) {
            $validator->errors()->add('led_by', sprintf('Selected member is not eligible to lead a Tier-%s team, as they don\'t have a character with such clearance!', $tier));
            throw new ValidationException($validator);
        }

        $team = new Team();
        $team->name = $request->get('name');
        $team->tier = $tier;
        $team->discord_id = $request->get('discord_id');
        $team->created_by = app('auth.driver')->id();
        $team->led_by = $led_by;
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
        app('cache.store')->has('team-' . $teamId); // Trigger Recache listener.
        $team = app('cache.store')->get('team-' . $teamId);
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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, int $teamId): JsonResponse
    {
        $this->authorize('user', User::class);

        $query = Team::query()->whereId($teamId);
        if (Gate::denies('is-admin')) {
            $myId = app('auth.driver')->id();
            $query->where(static function (Builder $query) use ($myId) {
                $query->where('led_by', $myId)->orWhere('created_by', $myId);
            });
        }
        /** @var Team $team */
        $team = $query->firstOrFail();

        $validatorErrorMessages = [
            'name.required' => 'Team name is required.',
            'tier.required' => 'Choose a tier for the content this team is specifialized in.',
            'tier.between' => 'Tier must be from 1 to 4.',
            'discord_id.required' => 'Discord Role-ID is required.',
            'led_by.required' => 'Choose a team leader.',
        ];
        $validator = app('validator')->make($request->all(), [
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
        $team->created_by = app('auth.driver')->id();
        $team->led_by = $request->get('led_by');
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

        $query = Team::query()->whereId($teamId);
        if (Gate::denies('is-admin')) {
            $myId = app('auth.driver')->id();
            $query->where(static function (Builder $query) use ($myId) {
                $query->where('led_by', $myId)->orWhere('created_by', $myId);
            });
        }
        /** @var Team $team */
        $team = $query->firstOrFail();

        $team->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $this->authorize('user', User::class);

        $characterIds = Character::query()
            ->whereUserId(Auth::id())
            ->orderBy('name')
            ->get(['id'])->pluck('id');

        $characters = collect();
        foreach ($characterIds as $characterId) {
            app('cache.store')->has('character-' . $characterId); // Trigger Recache listener.
            $character = app('cache.store')->get('character-' . $characterId);
            $characters->add($character);
        }

        return response()->json($characters);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('user', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'role' => 'required|integer|min:1|max:4',
            'class' => 'required|integer|min:1|max:6',
            'content' => 'nullable|array',
            'content.*' => 'nullable|numeric|exists:content,id',
            'sets' => 'required|array',
            'sets.*' => 'required|numeric|exists:sets,id',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|numeric|exists:skills,id',
        ], [
            'name.required' => 'Character name is required.',
            'name.string' => 'Character name must be string.',
            'role.required' => 'Choose a role.',
            'role.integer' => 'Role must be an integer.',
            'role.min' => 'Role must be an integer between 1 and 4.',
            'role.max' => 'Role must be an integer between 1 and 4.',
            'class.required' => 'Choose a class.',
            'class.integer' => 'Class must be an integer.',
            'class.min' => 'Class must be an integer between 1 and 6.',
            'class.max' => 'Class must be an integer between 1 and 6.',
            'content.array' => 'Content must be an array of integers.',
            'content.*.numeric' => 'Content must be an integer.',
            'content.*.exists' => 'One or more of given content doesn\'t exist.',
            'sets.required' => 'Select all full sets your Character has.',
            'sets.array' => 'Sets must be an array of integers.',
            'sets.*.required' => 'Select all full sets your Character has.',
            'sets.*.numeric' => 'Set must be an integer.',
            'sets.*.exists' => 'One or more of given sets doesn\'t exist.',
            'skills.array' => 'Skills must be an array of integers.',
            'skills.*.required' => 'Select all support skills your Character has unlocked and fully leveled.',
            'skills.*.exists' => 'One or more of given skills doesn\'t exist.',
            'skills.*.numeric' => 'Skill must be an integer.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $character = new Character();
        $character->user_id = Auth::id();
        $character->name = $request->get('name');
        $character->role = $request->get('role');
        $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->skills = !empty($request->get('skills')) ? implode(',', $request->get('skills')) : null;
        $character->save();

        if (!empty($request->get('content'))) {
            $charactersContent = array_filter($request->get('content'), static function ($item) {
                return !empty($item);
            });
            if (!empty($charactersContent)) {
                $character->content()->sync($charactersContent);
            }
        }

        $character->save();

        Cache::has('character-' . $character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $character->id), JsonResponse::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int     $characterId
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'role' => 'sometimes|required|integer|min:1|max:4',
            'class' => 'sometimes|required|integer|min:1|max:6',
            'content' => 'nullable|array',
            'content.*' => 'nullable|numeric|exists:content,id',
            'sets' => 'sometimes|required|array',
            'sets.*' => 'required|numeric|exists:sets,id',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|numeric|exists:skills,id',
        ], [
            'name.required' => 'Character name is required.',
            'name.string' => 'Character name must be string.',
            'role.required' => 'Choose a role.',
            'role.integer' => 'Role must be an integer.',
            'role.min' => 'Role must be an integer between 1 and 4.',
            'role.max' => 'Role must be an integer between 1 and 4.',
            'class.required' => 'Choose a class.',
            'class.integer' => 'Class must be an integer.',
            'class.min' => 'Class must be an integer between 1 and 6.',
            'class.max' => 'Class must be an integer between 1 and 6.',
            'content.array' => 'Content must be an array of integers.',
            'content.*.numeric' => 'Content must be an integer.',
            'content.*.exists' => 'One or more of given content doesn\'t exist.',
            'sets.required' => 'Select all full sets your Character has.',
            'sets.array' => 'Sets must be an array of integers.',
            'sets.*.required' => 'Select all full sets your Character has.',
            'sets.*.numeric' => 'Set must be an integer.',
            'sets.*.exists' => 'One or more of given sets doesn\'t exist.',
            'skills.array' => 'Skills must be an array of integers.',
            'skills.*.required' => 'Select all support skills your Character has unlocked and fully leveled.',
            'skills.*.exists' => 'One or more of given skills doesn\'t exist.',
            'skills.*.numeric' => 'Skill must be an integer.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $myId = Auth::id();
        $character = Character::query()->where(static function (Builder $query) use ($characterId, $myId) {
            $query
                ->where('user_id', $myId)
                ->where('id', $characterId);
        })->first();
        if (!$character) {
            throw new ModelNotFoundException('Character not found!');
        }
        $character->user_id = $myId;
        $request->filled('name') && $character->name = $request->get('name');
        $character->approved_for_tier === 0 && $request->filled('role') && $character->role = $request->get('role');
        $character->approved_for_tier === 0 && $request->filled('class') && $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->skills = !empty($request->get('skills')) ? implode(',', $request->get('skills')) : null;

        if (!empty($request->get('content'))) {
            $charactersContent = array_filter($request->get('content'), static function ($item) {
                return !empty($item);
            });
            $character->content()->sync($charactersContent);
        }

        $character->save();

        Cache::has('character-' . $character->id); // Recache trigger.

        return response()->json(Cache::get('character-' . $character->id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $characterId
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);

        $character = Character::query()
            ->whereUserId(Auth::id())
            ->whereApprovedForTier(0)
            ->whereId($characterId)
            ->first();
        if ($character) {
            $character->delete();
        } else {
            throw new ModelNotFoundException('Character not found!');
        }

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}

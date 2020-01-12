<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\User;
use App\Traits\Character\HasOrIsDpsParse;
use App\Traits\Character\IsCharacter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CharactersController extends Controller
{
    use IsCharacter, HasOrIsDpsParse;

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
            ->whereUserId(app('auth.driver')->id())
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
            'content.*' => 'nullable|numeric|exists:content,id',
            'sets.*' => 'required|numeric|exists:sets,id',
            'skills.*' => 'nullable|numeric|exists:skills,id',
        ], [
            'name.required' => 'Character name is required.',
            'role.required' => 'Choose a role.',
            'class.required' => 'Choose a class.',
            'content.*.required' => 'Select all content this Character has cleared.',
            'sets.*.required' => 'Select all full sets your Character has.',
            'skills.*.required' => 'Select all support skills your Character has unlocked and fully leveled.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $character = new Character();
        $character->user_id = app('auth.driver')->id();
        $character->name = $request->get('name');
        $character->role = $request->get('role');
        $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->skills = !empty($request->get('skills')) ? implode(',', $request->get('skills')) : null;
        $character->save();

        $charactersContent = array_filter($request->get('content'), static function ($item) {
            return !empty($item);
        });

        if (!empty($charactersContent)) {
            $character->content()->sync($charactersContent);
            $character->save();
        }

        return response()->json(['lastInsertId' => $character->id], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int     $characterId
     *
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, int $characterId): JsonResponse
    {
        $this->authorize('user', User::class);
        $validatorErrorMessages = [
            'name.required' => 'Character name can\'t be empty.',
            'role.required' => 'Choose a role.',
            'class.required' => 'Choose a class.',
            'content.*.required' => 'Select all content this Character has cleared.',
            'sets.*.required' => 'Select all full sets your Character has.',
            'skills.*.required' => 'Select all support skills your Character has unlocked and fully leveled.',
        ];
        $validator = app('validator')->make($request->all(), [
            'name' => 'sometimes|required|string',
            'role' => 'sometimes|required|integer|min:1|max:4',
            'class' => 'sometimes|required|integer|min:1|max:6',
            'content.*' => 'nullable|numeric|exists:content,id',
            'sets.*' => 'required|numeric|exists:sets,id',
            'skills.*' => 'nullable|numeric|exists:skills,id',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $character = Character::query()->where(static function (Builder $query) use ($characterId) {
            $query
                ->where('user_id', app('auth.driver')->id())
                ->where('id', $characterId);
        })->first(['id', 'name', 'class', 'role', 'sets']);
        if (!$character) {
            throw new ModelNotFoundException('Character not found!');
        }
        $character->user_id = app('auth.driver')->id();
        $request->filled('name') && $character->name = $request->get('name');
        $request->filled('role') && $character->role = $request->get('role');
        $request->filled('class') && $character->class = $request->get('class');
        $character->sets = !empty($request->get('sets')) ? implode(',', $request->get('sets')) : null;
        $character->skills = !empty($request->get('skills')) ? implode(',', $request->get('skills')) : null;

        $charactersContent = array_filter($request->get('content'), static function ($item) {
            return !empty($item);
        });
        $character->content()->sync($charactersContent);

        $character->save();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
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
            ->whereUserId(app('auth.driver')->id())
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

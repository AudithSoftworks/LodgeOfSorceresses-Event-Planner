<?php namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\User\IsUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{
    use IsUser;

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $this->authorize('user', User::class);

        $userIds = User::query()->whereNotNull('name')->orderBy('name')->get(['id'])->pluck('id');
        $users = collect();
        foreach ($userIds as $userId) {
            Cache::has('user-' . $userId); // Trigger Recache listener.
            $character = Cache::get('user-' . $userId);
            $character !== null && $users->add($character);
        }

        return response()->json($users);
    }

    /**
     * @param int $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $userId): JsonResponse
    {
        $this->authorize('user', User::class);

        Cache::has('user-' . $userId); // Recache trigger

        return response()->json(Cache::get('user-' . $userId));
    }
}

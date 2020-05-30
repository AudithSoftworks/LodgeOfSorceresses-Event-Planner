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

        $users = User::query()->whereNotNull('name')->orderBy('name')->get(['id', 'name']);
        foreach ($users as $user) {
            $this->parseLinkedAccounts($user);
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

<?php namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Users\IsUser;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    use IsUser;

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
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

    public function show(int $user): JsonResponse
    {
        $cacheStore = app('cache.store');
        $cacheStore->has('user-' . $user);

        return response()->json($cacheStore->get('user-' . $user));
    }
}

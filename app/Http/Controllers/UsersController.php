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
        $userIds = User::query()->whereNotNull('name')->orderBy('name')->get(['id'])->pluck('id');
        $users = collect();
        foreach ($userIds as $userId) {
            app('cache.store')->has('user-' . $userId);
            $user = app('cache.store')->get('user-' . $userId);
            $users->add($user);
        }

        return response()->json($users);
    }
}

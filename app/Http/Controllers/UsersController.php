<?php namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Users\IsUser;
use Illuminate\Database\Eloquent\Builder;
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
        $users = User::with('linkedAccounts')
            ->whereHas('linkedAccounts', static function (Builder $query) {
                $query->where('remote_provider', '=', 'discord')->whereNotNull('remote_secondary_groups');
            })
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();
        foreach ($users as &$user) {
            $this->parseLinkedAccounts($user);
        }

        return response()->json($users);
    }
}

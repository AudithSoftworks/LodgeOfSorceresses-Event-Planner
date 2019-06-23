<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function me(): JsonResponse
    {
        $this->authorize('user', User::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->loadMissing('linkedAccounts');
        /** @noinspection PhpUndefinedFieldInspection */
        $me->linkedAccountsParsed = $me->linkedAccounts->keyBy('remote_provider');
        $me->makeVisible(['linkedAccountsParsed']);
        $me->makeHidden(['linkedAccounts']);

        return response()->json($me);
    }
}

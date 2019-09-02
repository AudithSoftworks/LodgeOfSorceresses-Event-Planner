<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): JsonResponse
    {
        $guard = app('auth.driver');

        if (!$guard->check()) {
            return response()->json([], JsonResponse::HTTP_UNAUTHORIZED);
        }

        /** @var \App\Models\User $me */
        $me = $guard->user();
        $me->loadMissing('linkedAccounts');
        $linkedAccountsParsed = $me->linkedAccounts->keyBy('remote_provider');
        foreach ($linkedAccountsParsed as $linkedAccount) {
            !empty($linkedAccount->remote_secondary_groups) && $linkedAccount->remote_secondary_groups = explode(',', $linkedAccount->remote_secondary_groups);
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $me->linkedAccountsParsed = $linkedAccountsParsed;
        $me->makeVisible(['linkedAccountsParsed']);
        $me->makeHidden(['linkedAccounts']);

        return response()->json($me);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateName(): JsonResponse
    {
        $this->authorize('user', User::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
    }
}

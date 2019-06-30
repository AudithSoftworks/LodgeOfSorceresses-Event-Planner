<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            return response()->json();
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
}

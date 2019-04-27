<?php namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): JsonResponse
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->load('linkedAccounts');
        /** @noinspection PhpUndefinedFieldInspection */
        $me->linkedAccountsParsed = $me->linkedAccounts->keyBy('remote_provider');
        $me->makeVisible(['linkedAccountsParsed']);
        $me->makeHidden(['linkedAccounts']);

        return response()->json($me);
    }
}

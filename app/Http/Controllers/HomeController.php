<?php namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = app('auth.driver')->user();
        $name = $user->name;
        $userType = self::TRANSLATION_TAG_REGISTERED_USER;

        return view('index', ['userType' => $userType, 'name' => $name]);
    }

    public function getDiscordOauthAccount(): JsonResponse
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->load('linkedAccounts');

        $discordOauthAccount = $me->linkedAccounts()->where('remote_provider', 'discord')->first();

        return response()->json(['discordOauthAccount' => $discordOauthAccount]);
    }
}

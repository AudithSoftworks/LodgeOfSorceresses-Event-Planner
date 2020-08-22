<?php

namespace App\Listeners\User;

use App\Events\User\LoggedIn;

class TrackUserLoginForSqreen
{
    public function handle(LoggedIn $event): bool
    {
        $user = $event->getUser();
        $parsedEmail = 'anon-' . $user->id . strstr($user->email, '@');
        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUndefinedFunctionInspection */
        \sqreen\auth_track($user !== null, ['email' => $parsedEmail, 'platform_id' => $user->id]);

        return true;
    }
}

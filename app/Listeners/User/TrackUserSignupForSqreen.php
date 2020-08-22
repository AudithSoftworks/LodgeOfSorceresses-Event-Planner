<?php

namespace App\Listeners\User;

use App\Events\User\Registered;

class TrackUserSignupForSqreen
{
    public function handle(Registered $event): bool
    {
        $user = $event->getUser();
        $parsedEmail = 'anon-' . $user->id . strstr($user->email, '@');
        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUndefinedFunctionInspection */
        \sqreen\signup_track(['email' => $parsedEmail, 'platform_id' => $user->id]);

        return true;
    }
}

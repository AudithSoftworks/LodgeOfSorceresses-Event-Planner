<?php

namespace App\Listeners\User;

use App\Events\User\OnboardingCompleted;
use App\Services\DiscordApi;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Gate;

class AnnounceOnboardingCompletionOnDiscord
{
    /**
     * @param \App\Events\User\OnboardingCompleted $event
     *
     * @throws \JsonException
     * @return bool
     */
    public function handle(OnboardingCompleted $event): bool
    {
        if (($user = $event->getOwner()) === null) {
            return false;
        }
        $user->loadMissing(['linkedAccounts']);

        $membershipMode = Gate::forUser($user)->allows('is-member') ? DiscordApi::ROLE_MEMBERS : null;
        if ($membershipMode === null) {
            $membershipMode = Gate::forUser($user)->allows('is-soulshriven') ? DiscordApi::ROLE_SOULSHRIVEN : null;
        }
        if ($membershipMode === null) {
            return false;
        }

        $discordApi = app('discord.api');
        $channelId = config('services.discord.channels.officer_logs');
        /** @var null|\App\Models\UserOAuth $parseOwnersDiscordAccount */
        $parseOwnersDiscordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        if ($parseOwnersDiscordAccount) {
            $mentionedName = '<@!' . $parseOwnersDiscordAccount->remote_id . '>';
            $discordApi->createMessageInChannel($channelId, [
                RequestOptions::FORM_PARAMS => [
                    'payload_json' => json_encode([
                        'content' => sprintf(
                            '%s have completed their onboarding as a **%s**.',
                            $mentionedName,
                            $membershipMode === DiscordApi::ROLE_MEMBERS ? 'Member' : 'Soulshriven'
                        ),
                        'tts' => false,
                    ], JSON_THROW_ON_ERROR),
                ],
            ]);
        }

        return true;
    }
}

<?php namespace App\Listeners\User;

use App\Events\User\LoggedInViaIpsOauth;
use Carbon\Carbon;

class UpdateOauthUserDataViaDiscordApi
{
    private const OAUTH_USER_UPDATE_TIMEOUT = 900; // in seconds

    /**
     * @param \App\Events\User\LoggedInViaIpsOauth $event
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(LoggedInViaIpsOauth $event): bool
    {
        $oauthAccount = $event->oauthAccount;
        $oauthAccount->load(['owner']);
        /** @var \App\Models\User $user */
        $user = $oauthAccount->owner()->first();
        $user->load(['linkedAccounts']);
        $discordAccount = $user->linkedAccounts()->where('remote_provider', 'discord')->first();
        if (!$discordAccount) {
            return false;
        }

        $now = new Carbon();
        $updatedAt = new Carbon($discordAccount->updated_at);
        $oauthAccountUpdateTimeout = env('OAUTH_USER_UPDATE_TIMEOUT', self::OAUTH_USER_UPDATE_TIMEOUT);
        if ($event->forceOauthUpdateViaApi || $now->diffAsCarbonInterval($updatedAt)->totalSeconds > $oauthAccountUpdateTimeout) {
            $remoteUserDataFetchedThroughApi = app('discord.api')->getGuildMember($discordAccount->remote_id);
            $discordAccount->remote_secondary_groups = count($remoteUserDataFetchedThroughApi['roles'])
                ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
                : null;
            $discordAccount->touch();
            $discordAccount->save();
        }

        return true;
    }
}

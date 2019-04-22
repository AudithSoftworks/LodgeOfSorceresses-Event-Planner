<?php namespace App\Listeners\User;

use App\Events\User\LoggedInViaOauth;

class UpdateOauthUserDataViaDiscordApi
{
    /**
     * @param \App\Events\User\LoggedInViaOauth $event
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(LoggedInViaOauth $event): bool
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

        $remoteUserDataFetchedThroughApi = app('discord.api')->getGuildMember($discordAccount->remote_id);
        $discordAccount->remote_secondary_groups = count($remoteUserDataFetchedThroughApi['roles'])
            ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
            : null;
        $discordAccount->touch();
        $discordAccount->save();

        return true;
    }
}

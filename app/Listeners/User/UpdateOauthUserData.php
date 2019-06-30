<?php namespace App\Listeners\User;

use App\Events\User\LoggedIn;

class UpdateOauthUserData
{
    /**
     * @param \App\Events\User\LoggedIn $event
     *
     * @return bool
     */
    public function handle(LoggedIn $event): bool
    {
        $user = $event->user;
        foreach ($linkedAccounts = $user->linkedAccounts()->get() as $linkedAccount) {
            if ($linkedAccount->remote_provider === 'ips') {
                $remoteUserDataFetchedThroughApi = app('ips.api')->getUser($linkedAccount->remote_id);
                $remoteSecondaryGroups = array_reduce($remoteUserDataFetchedThroughApi['secondaryGroups'], static function ($acc, $item) {
                    $acc === null && $acc = [];
                    $acc[] = $item['id'];

                    return $acc;
                });
                $linkedAccount->remote_primary_group = $remoteUserDataFetchedThroughApi['primaryGroup']['id'];
                $linkedAccount->remote_secondary_groups = $remoteSecondaryGroups ? implode(',', $remoteSecondaryGroups) : null;
            } elseif ($linkedAccount->remote_provider === 'discord') {
                $remoteUserDataFetchedThroughApi = app('discord.api')->getGuildMember($linkedAccount->remote_id);
                $linkedAccount->remote_secondary_groups = count($remoteUserDataFetchedThroughApi['roles'])
                    ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
                    : null;
            }
            $linkedAccount->save();
        }

        return true;
    }
}

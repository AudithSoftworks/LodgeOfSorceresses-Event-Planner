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
        $user->refresh();
        /** @var \App\Models\UserOAuth $linkedAccount */
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

                if (empty($user->avatar) && !empty($remoteUserDataFetchedThroughApi['photoUrl'])) {
                    $user->avatar = $remoteUserDataFetchedThroughApi['photoUrl'];
                }
            } elseif ($linkedAccount->remote_provider === 'discord') {
                $remoteUserDataFetchedThroughApi = app('discord.api')->getGuildMember($linkedAccount->remote_id);
                $linkedAccount->remote_secondary_groups = count($remoteUserDataFetchedThroughApi['roles'])
                    ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
                    : null;

                $avatarHash = $remoteUserDataFetchedThroughApi['user']['avatar'] ?? null;
                $avatarExtension = strpos($avatarHash, 'a_') === 0 ? 'gif' : 'png';
                if (empty($user->avatar) && !empty($avatarHash)) {
                    $user->avatar = sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s?size=256', $linkedAccount->remote_id, $avatarHash, $avatarExtension);
                }
            }
            $linkedAccount->save();
        }
        if ($user->isDirty()) {
            $user->save();
        }

        return true;
    }
}

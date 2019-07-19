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
            $userHasNoAvatar = empty($user->avatar);
            if ($linkedAccount->remote_provider === 'ips') {
                $remoteUserDataFetchedThroughApi = app('ips.api')->getUser($linkedAccount->remote_id);
                $remoteSecondaryGroups = array_reduce($remoteUserDataFetchedThroughApi['secondaryGroups'], static function ($acc, $item) {
                    $acc === null && $acc = [];
                    $acc[] = $item['id'];

                    return $acc;
                });
                $linkedAccount->remote_primary_group = $remoteUserDataFetchedThroughApi['primaryGroup']['id'];
                $linkedAccount->remote_secondary_groups = $remoteSecondaryGroups ? implode(',', $remoteSecondaryGroups) : null;

                if (!empty($remoteUserDataFetchedThroughApi['photoUrl'])) {
                    $hasUserAvatarChanged = $user->avatar !== $remoteUserDataFetchedThroughApi['photoUrl'] && false !== strpos($user->avatar, 'amazonaws');
                    if ($userHasNoAvatar || $hasUserAvatarChanged) {
                        $user->avatar = $remoteUserDataFetchedThroughApi['photoUrl'];
                    }
                }
            } elseif ($linkedAccount->remote_provider === 'discord') {
                $remoteUserDataFetchedThroughApi = app('discord.api')->getGuildMember($linkedAccount->remote_id);
                $linkedAccount->remote_secondary_groups = count($remoteUserDataFetchedThroughApi['roles'])
                    ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
                    : null;

                $avatarHash = $remoteUserDataFetchedThroughApi['user']['avatar'] ?? null;
                $avatarHash && $avatarExtension = strpos($avatarHash, 'a_') === 0 ? 'gif' : 'png';
                isset($avatarExtension) && $avatarUrl = sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s?size=256', $linkedAccount->remote_id, $avatarHash, $avatarExtension);
                if (isset($avatarUrl)) {
                    $hasUserAvatarChanged = $user->avatar !== $avatarUrl && false !== strpos($user->avatar, 'cdn.discordapp.com');
                    if ($userHasNoAvatar || $hasUserAvatarChanged) {
                        $user->avatar = $avatarUrl;
                    }
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

<?php

namespace App\Console\Commands;

use App\Models\UserOAuth;
use App\Services\DiscordApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncOauthAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes OAuth accounts and deletes User accounts with no OAuth linking.';

    /**
     * Execute the console command.
     *
     * @throws \Exception
     * @return bool
     */
    public function handle(): bool
    {
        $oauthAccounts = UserOAuth::query()->get();
        /** @var UserOAuth $oauthAccount */
        $oauthAccount = $oauthAccounts->shift();
        while ($oauthAccount) {
            if ($user = $oauthAccount->owner) {
                switch ($oauthAccount->remote_provider) {
                    case 'ips':
                        if ($this->syncIpsMember($oauthAccount) === false) {
                            $this->warn('[' . $user->name . ']' . ' User not found on IPS. Deleting this IPS OAuth link...');
                            $oauthAccount->delete();
                            $this->info('[' . $user->name . ']' . ' Deleted.');
                            Cache::forget('user-' . $user->id);
                        }
                        break;
                    case 'discord':
                        if ($this->syncDiscordMember($oauthAccount) === false) {
                            $this->warn('[' . $user->name . ']' . ' User not found on Discord or found without roles. Deleting user completely...');
                            $oauthAccount->delete();
                            $user->delete();
                            $this->info('[' . $user->name . ']' . ' Deleted.');
                            Cache::forget('user-' . $user->id);
                        }
                        break;
                }
            }

            $oauthAccount = $oauthAccounts->shift();
        }

        $this->info('OAuth accounts successfully synced!');

        return true;
    }

    private function syncIpsMember(UserOAuth $oauthAccount): bool
    {
        $remoteUserDataFetchedThroughApi = app('ips.api')->getUser($oauthAccount->remote_id);
        if (!$remoteUserDataFetchedThroughApi) {
            return false;
        }
        $oauthAccount->name = $remoteUserDataFetchedThroughApi['name'];
        $oauthAccount->nickname = null;
        $oauthAccount->remote_primary_group = $remoteUserDataFetchedThroughApi['primaryGroup']['id'];
        $oauthAccount->remote_secondary_groups = null; // We don't use secondary groups on IPS anymore, everyone are Members.
        $user = $oauthAccount->owner;
        if ($oauthAccount->isDirty()) {
            $oauthAccount->save();
            Cache::forget('user-' . $user->id);
        }
        $this->info('[' . $user->name . ']' . ' Successfully synced via IPS.');

        return true;
    }

    private function syncDiscordMember(UserOAuth $oauthAccount): bool
    {
        $discordApi = app('discord.api');
        $remoteUserDataFetchedThroughApi = $discordApi->getGuildMember($oauthAccount->remote_id);
        if ($remoteUserDataFetchedThroughApi === null) {
            return false;
        }

        if (
            collect($remoteUserDataFetchedThroughApi['roles'])
                ->intersect([DiscordApi::ROLE_SOULSHRIVEN, DiscordApi::ROLE_MEMBERS])
                ->count() === 0
        ) {
            return false;
        }

        $oauthAccount->name = $remoteUserDataFetchedThroughApi['user']['username'] . '#' . $remoteUserDataFetchedThroughApi['user']['discriminator'];
        $oauthAccount->nickname = $remoteUserDataFetchedThroughApi['nick'];
        $oauthAccount->remote_secondary_groups = !empty($remoteUserDataFetchedThroughApi['roles'])
            ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
            : null;

        $user = $oauthAccount->owner;
        $userHasNoAvatar = empty($user->avatar);
        $avatarHash = $remoteUserDataFetchedThroughApi['user']['avatar'] ?? null;
        $avatarHash && $avatarExtension = strpos($avatarHash, 'a_') === 0 ? 'gif' : 'png';
        $avatarHash = preg_replace('/^a_/', '', $avatarHash);
        isset($avatarExtension) && $avatarUrl = sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s?size=256', $oauthAccount->remote_id, $avatarHash, $avatarExtension);
        if (isset($avatarUrl)) {
            $hasUserAvatarChanged = $user->avatar !== $avatarUrl && false !== strpos($user->avatar, 'cdn.discordapp.com');
            if ($userHasNoAvatar || $hasUserAvatarChanged) {
                $user->avatar = $avatarUrl;
            }
        }

        $oauthAccount->isDirty() && $oauthAccount->save();
        if ($user->isDirty()) {
            $user->save();
            Cache::forget('user-' . $user->id);
        }
        $this->info('[' . $user->name . ']' . ' Successfully synced via Discord.');

        return true;
    }
}

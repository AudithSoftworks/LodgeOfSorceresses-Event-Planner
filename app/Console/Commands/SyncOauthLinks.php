<?php

namespace App\Console\Commands;

use App\Models\UserOAuth;
use App\Services\IpsApi;
use Illuminate\Console\Command;

class SyncOauthLinks extends Command
{
    private const MEMBER_FOUND = 200;

    private const MEMBER_NOT_FOUND = 404;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:sync-oauth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes OAuth accounts and deletes User accounts with no OAuth linking.';

    /**
     * @var \App\Services\DiscordApi
     */
    private $discordApi;

    /**
     * @var \App\Services\IpsApi
     */
    private $ipsApi;

    /**
     * @var \Illuminate\Support\Collection|\App\Models\User[]
     */
    private $usersMarkedForReexamination;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->discordApi = app('discord.api');
        $this->ipsApi = app('ips.api');
        $this->usersMarkedForReexamination = collect();
    }

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        /** @var UserOAuth|\Illuminate\Database\Eloquent\Collection $oauthAccount */
        $oauthAccounts = UserOAuth::query()->get();
        $oauthAccount = $oauthAccounts->shift();
        while ($oauthAccount) {
            switch ($oauthAccount->remote_provider) {
                case 'discord':
                    if ($this->syncDiscordMember($oauthAccount) === self::MEMBER_NOT_FOUND) {
                        $this->warn('[' . $oauthAccount->name . ']' . ' Member not found on Discord, deleting this OAuth link & marking this user for re-examination...');
                        $this->deleteOauthAccountAndAddUserToReexaminationList($oauthAccount);
                    }
                    break;
                case 'ips':
                    if ($this->syncIpsMember($oauthAccount) === self::MEMBER_NOT_FOUND) {
                        $this->warn('[' . $oauthAccount->name . ']' . ' Member not found on IPS, deleting this OAuth link & marking this user for re-examination...');
                        $this->deleteOauthAccountAndAddUserToReexaminationList($oauthAccount);
                    }
                    break;
            }

            $oauthAccount = $oauthAccounts->shift();
        }

        $this->info('OAuth accounts successfully synced! Processing re-examination list...');

        $this->processUsersListedForReexamination();

        $this->info('Command successfully executed!');

        return true;
    }

    private function syncDiscordMember(UserOAuth $oauthAccount): int
    {
        $remoteUserDataFetchedThroughApi = $this->discordApi->getGuildMember($oauthAccount->remote_id);
        if ($remoteUserDataFetchedThroughApi === null) {
            return self::MEMBER_NOT_FOUND;
        }
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
            app('cache.store')->forget('user-' . $user->id);
        }
        $this->info('[' . $oauthAccount->name . ']' . ' Successfully synced via Discord.');

        return self::MEMBER_FOUND;
    }

    private function syncIpsMember(UserOAuth $oauthAccount): int
    {
        $remoteUserDataFetchedThroughApi = $this->ipsApi->getUser($oauthAccount->remote_id);
        if (!$remoteUserDataFetchedThroughApi) {
            return self::MEMBER_NOT_FOUND;
        }
        $remoteSecondaryGroups = array_reduce($remoteUserDataFetchedThroughApi['secondaryGroups'], static function ($acc, $item) {
            $acc[] = $item['id'];

            return $acc;
        }, []);
        $oauthAccount->remote_primary_group = $remoteUserDataFetchedThroughApi['primaryGroup']['id'];
        $oauthAccount->remote_secondary_groups = $remoteSecondaryGroups ? implode(',', $remoteSecondaryGroups) : null;
        if ($oauthAccount->isDirty()) {
            $oauthAccount->save();
            $user = $oauthAccount->owner;
            app('cache.store')->forget('user-' . $user->id);
        }
        $this->info('[' . $oauthAccount->name . ']' . ' Successfully synced via IPS.');

        return self::MEMBER_FOUND;
    }

    /**
     * @param \App\Models\UserOAuth $oauthAccount
     */
    private function deleteOauthAccountAndAddUserToReexaminationList(UserOAuth $oauthAccount): void
    {
        $user = $oauthAccount->owner;
        $oauthAccount->forceDelete();
        if ($user) {
            $user->loadMissing(['linkedAccounts'])->refresh();
            if (!$this->usersMarkedForReexamination->has($user->id)) {
                $this->usersMarkedForReexamination->put($user->id, $user);
                $this->info('[@' . $user->name . ']' . ' Added for re-examination.');
            } else {
                $this->usersMarkedForReexamination->replace([$user->id => $user]);
                $this->warn('[@' . $user->name . ']' . ' Updated in re-examination list!');
            }
        }
    }

    private function processUsersListedForReexamination(): void
    {
        foreach ($this->usersMarkedForReexamination as $user) {
            $user->loadMissing(['linkedAccounts'])->refresh();
            $linkedAccounts = $user->linkedAccounts;
            if ($linkedAccounts->count()) {
                /** @var null|\App\Models\UserOAuth $ipsOauthAccount */
                $ipsOauthAccount = $linkedAccounts->firstWhere('remote_provider', 'ips');
                /** @var null|\App\Models\UserOAuth $discordOauthAccount */
                $discordOauthAccount = $linkedAccounts->firstWhere('remote_provider', 'discord');
                if ($ipsOauthAccount !== null && $discordOauthAccount === null) {
                    $this->warn('[' . $ipsOauthAccount->name . ']' . ' User has left Discord and has IPS account. Setting them as Soulshriven on IPS...');
                    $this->ipsApi->editUser($ipsOauthAccount->remote_id, ['group' => IpsApi::MEMBER_GROUPS_SOULSHRIVEN, 'secondaryGroups' => []]);
                    $this->warn('[@' . $user->name . ']' . ' Now deleting them on Planner...');
                    $user->forceDelete();
                    $this->info('[@' . $user->name . ']' . ' Deleted.');
                }
            } else {
                $this->warn('[@' . $user->name . ']' . ' User has no remaining OAuth links. Deleting...');
                $user->forceDelete();
                $this->info('[@' . $user->name . ']' . ' Deleted.');
            }
            app('cache.store')->forget('user-' . $user->id);
        }

        !$this->usersMarkedForReexamination->count() ? $this->warn('Nothing to re-examine!') : $this->info('Re-examination completed!');
    }
}

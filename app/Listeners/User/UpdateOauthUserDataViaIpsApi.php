<?php namespace App\Listeners\User;

use App\Events\User\LoggedInViaIpsOauth;
use Carbon\Carbon;

class UpdateOauthUserDataViaIpsApi
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

        $now = new Carbon();
        $updatedAt = new Carbon($oauthAccount->updated_at);
        $oauthAccountUpdateTimeout = env('OAUTH_USER_UPDATE_TIMEOUT', self::OAUTH_USER_UPDATE_TIMEOUT);
        if ($event->forceOauthUpdateViaApi || $now->diffAsCarbonInterval($updatedAt)->totalSeconds > $oauthAccountUpdateTimeout) {
            $remoteUserDataFetchedThroughApi = app('ips.api')->getUser($oauthAccount->remote_id);
            $remoteSecondaryGroups = array_reduce($remoteUserDataFetchedThroughApi['secondaryGroups'], static function ($acc, $item) {
                $acc === null && $acc = [];
                $acc[] = $item['id'];

                return $acc;
            });
            $oauthAccount->remote_primary_group = $remoteUserDataFetchedThroughApi['primaryGroup']['id'];
            $oauthAccount->remote_secondary_groups = $remoteSecondaryGroups ? implode(',', $remoteSecondaryGroups) : null;
            $oauthAccount->touch();
            $oauthAccount->save();
        }

        return true;
    }
}

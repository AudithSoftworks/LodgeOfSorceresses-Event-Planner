<?php namespace App\Listeners\User;

use App\Events\Users\LoggedInViaIpsOauth;
use Carbon\Carbon;

class UpdateOauthUserDataViaIpsApi
{
    private const IPS_OAUTH_USER_UPDATE_TIMEOUT = 900; // in seconds

    /**
     * @param \App\Events\Users\LoggedInViaIpsOauth $event
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(LoggedInViaIpsOauth $event): bool
    {
        $oauthAccount = $event->oauthAccount;

        $now = new Carbon();
        $updatedAt = new Carbon($oauthAccount->updated_at);
        $oauthAccountUpdateTimeout = env('IPS_OAUTH_USER_UPDATE_TIMEOUT', self::IPS_OAUTH_USER_UPDATE_TIMEOUT);
        if ($event->forceOauthUpdateViaApi || $now->diffAsCarbonInterval($updatedAt)->totalSeconds > $oauthAccountUpdateTimeout) {
            $remoteUserDataFetchedThroughApi = app('ips.api')->getUser($oauthAccount->remote_id);
            $remoteSecondaryGroups = array_reduce($remoteUserDataFetchedThroughApi['secondaryGroups'], static function ($acc, $item) {
                $acc === null && $acc = [];
                $acc[] = $item['id'];

                return $acc;
            });
            if ($remoteSecondaryGroups) {
                $oauthAccount->remote_secondary_groups = implode(',', $remoteSecondaryGroups);
            }
            $oauthAccount->touch();
            $oauthAccount->save();
        }

        return true;
    }
}

<?php namespace App\Listeners\User;

use App\Events\User\LoggedInViaOauth;

class UpdateOauthUserDataViaIpsApi
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
        if ($oauthAccount->remote_provider !== 'ips') {
            return true;
        }

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

        return true;
    }
}

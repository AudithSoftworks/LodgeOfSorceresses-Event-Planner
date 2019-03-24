<?php

namespace App\Policies;

use App\Models\User;
use App\Services\IpsApi;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Relations\Relation;

class DpsParsePolicy
{
    use HandlesAuthorization;

    /**
     * @var \App\Models\UserOAuth
     */
    private $oauthAccount;

    public function __construct(User $user)
    {
        $this->oauthAccount = $user->with([
            'linkedAccounts' => function (Relation $query) {
                $query->where('remote_provider', '=', 'ips');
            }
        ])->first()->linkedAccounts()->first();
    }

    /**
     * @return bool
     */
    public function view()
    {
        return $this->oauthAccount->remotePrimaryAccount !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function create()
    {
        return $this->oauthAccount->remotePrimaryAccount !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function update()
    {
        return $this->oauthAccount->remotePrimaryAccount !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->oauthAccount->remotePrimaryAccount !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }
}

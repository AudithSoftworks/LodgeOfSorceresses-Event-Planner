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
        $userWithLinkedAccounts = $user->with([
            'linkedAccounts' => static function (Relation $query) {
                $query->where('remote_provider', '=', 'ips');
            }
        ])->first();
        if ($userWithLinkedAccounts) {
            $this->oauthAccount = $userWithLinkedAccounts->linkedAccounts()->first();
            $this->oauthAccount->remote_secondary_groups = explode(',', $this->oauthAccount->remote_secondary_groups);
        }
    }

    /**
     * @return bool
     */
    public function admin(): bool
    {
        return $this->oauthAccount
            && (
                $this->oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_IPSISSIMUS
                || $this->oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI
                || in_array(IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI, explode(',', $this->oauthAccount->remote_secondary_groups), false)
            );
    }

    /**
     * @return bool
     */
    public function view(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function create(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function update(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return $this->oauthAccount && $this->oauthAccount->remote_primary_group !== IpsApi::MEMBER_GROUPS_SOULSHRIVEN;
    }
}

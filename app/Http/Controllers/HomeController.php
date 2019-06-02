<?php namespace App\Http\Controllers;

use App\Models\User;
use App\Services\IpsApi;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('user', User::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->load('linkedAccounts');
        $oauthAccount = $me->linkedAccounts()->where('remote_provider', 'ips')->first();
        if ($oauthAccount) {
            $oauthAccount->remote_secondary_groups = explode(',', $oauthAccount->remote_secondary_groups);
            if ($oauthAccount && $oauthAccount->remote_primary_group === IpsApi::MEMBER_GROUPS_SOULSHRIVEN) {
                return redirect('https://lodgeofsorceresses.com');
            }
        } else {
            return redirect('https://lodgeofsorceresses.com');
        }

        return view('index');
    }
}

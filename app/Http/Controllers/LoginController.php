<?php namespace App\Http\Controllers;

use App\Events\User\LoggedIn;
use App\Events\User\LoggedOut;
use App\Events\User\Registered;
use App\Exceptions\Users\UserNotActivatedException;
use App\Exceptions\Users\UserNotMemberInDiscord;
use App\Extensions\Socialite\CustomOauthTwoUser;
use App\Models\User;
use App\Models\UserOAuth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Factory as SocialiteContract;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout', 'handleOAuthRedirect', 'handleOAuthReturn']]);
    }

    /**
     * @param \Laravel\Socialite\Contracts\Factory $socialite
     * @param string                               $provider
     *
     * @return RedirectResponse
     */
    public function handleOAuthRedirect(SocialiteContract $socialite, $provider): RedirectResponse
    {
        return $socialite->driver($provider)->redirect();
    }

    /**
     * Handle OAuth login.
     *
     * @param \Illuminate\Http\Request             $request
     * @param \Laravel\Socialite\Contracts\Factory $socialite
     * @param string                               $provider
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\Users\UserNotActivatedException
     * @throws \App\Exceptions\Users\UserNotMemberInDiscord
     */
    public function handleOAuthReturn(Request $request, SocialiteContract $socialite, $provider)
    {
        switch ($provider) {
            case 'ips':
                if (!$request->exists('code')) {
                    return back();
                }
                break;
            case 'discord':
                if (!$request->exists('code')) {
                    return redirect()->intended($this->redirectPath());
                }
                break;
        }

        /** @var CustomOauthTwoUser $oauthTwoUser */
        $oauthTwoUser = $socialite->driver($provider)->user();
        if ($this->loginViaOAuth($oauthTwoUser, $provider)) {
            !empty($oauthTwoUser->token) && $request->session()->put('token', $oauthTwoUser->token);
            !empty($oauthTwoUser->refreshToken) && $request->session()->put('refreshToken', $oauthTwoUser->refreshToken);

            return redirect()->intended($this->redirectPath());
        }

        return redirect('/');
    }

    /**
     * @param CustomOauthTwoUser $oauthTwoUser
     * @param string             $provider
     *
     * @return bool
     * @throws \App\Exceptions\Users\UserNotActivatedException
     * @throws \App\Exceptions\Users\UserNotMemberInDiscord
     * @throws \Exception
     */
    protected function loginViaOAuth(CustomOauthTwoUser $oauthTwoUser, string $provider): bool
    {
        if ($provider === 'discord') {
            if (!($discordUser = app('discord.api')->getGuildMember($oauthTwoUser->getId()))) {
                throw new UserNotMemberInDiscord('You need to join Lodge Discord server to continue! Please do so and come back afterwards.');
            }
            $discordUser['nick'] && $oauthTwoUser->nickname = $discordUser['nick'];
            !empty($discordUser['roles']) && $oauthTwoUser->remoteSecondaryGroups = $discordUser['roles'];
        }
        if ($provider === 'ips' && $ipsUser = app('ips.api')->getUser($oauthTwoUser->getId())) {
            $oauthTwoUser->verified = !(bool)$ipsUser['validating'];
            ($ipsUser['secondaryGroups'] ?? null) !== null && $oauthTwoUser->remoteSecondaryGroups = array_column($ipsUser['secondaryGroups'], 'id');
        }
        if (!$oauthTwoUser->isVerified()) {
            throw new UserNotActivatedException('Your Discord/Forums account hasn\'t been activated! Please activate it and come back afterwards.');
        }

        /** @var UserOAuth $owningOAuthAccount */
        if ($owningOAuthAccount = UserOAuth::whereRemoteProvider($provider)->whereRemoteId($oauthTwoUser->id)->first()) {
            $ownerAccount = $owningOAuthAccount->owner;
            Auth::login($ownerAccount);
            event(new LoggedIn($ownerAccount));

            $owningOAuthAccount->remote_secondary_groups !== $oauthTwoUser->remoteSecondaryGroups
            && $owningOAuthAccount->remote_secondary_groups = implode(',', $oauthTwoUser->getRemoteSecondaryGroups());
            $owningOAuthAccount->token = $oauthTwoUser->token;
            $owningOAuthAccount->token_expires_at = new Carbon(sprintf('+%d seconds', $oauthTwoUser->expiresIn));
            $owningOAuthAccount->refresh_token = $oauthTwoUser->refreshToken;
            $owningOAuthAccount->isDirty() && $owningOAuthAccount->save() && Cache::forget('user-' . $ownerAccount->id);

            return true;
        }

        return $this->registerViaOAuth($oauthTwoUser, $provider);
    }

    /**
     * @param \App\Extensions\Socialite\CustomOauthTwoUser $oauthTwoUser
     * @param string                                       $provider
     *
     * @return bool
     * @throws \Exception
     */
    protected function registerViaOAuth(CustomOauthTwoUser $oauthTwoUser, string $provider): bool
    {
        if (Auth::check()) {
            $ownerAccount = Auth::user();
        } else {
            $ownerAccount = User::withTrashed()->whereEmail($oauthTwoUser->email)->first();
            if (!$ownerAccount) {
                /** @noinspection PhpUndefinedMethodInspection */
                $ownerAccount = User::create([
                    'email' => $oauthTwoUser->getEmail(),
                    'password' => Hash::make(uniqid('', true))
                ]);
                event(new Registered($ownerAccount, $provider));
            }

            # If user account is soft-deleted, restore it.
            $ownerAccount->trashed() && $ownerAccount->restore();

            $ownerAccount->isDirty() && $ownerAccount->save();
        }
        $this->linkOAuthAccount($oauthTwoUser, $provider, $ownerAccount);
        Auth::login($ownerAccount, true);
        event(new LoggedIn($ownerAccount));

        return true;
    }

    /**
     * @param \App\Extensions\Socialite\CustomOauthTwoUser $oauthTwoUser
     * @param string                                       $provider
     * @param \App\Models\User                             $ownerAccount
     *
     * @throws \Exception
     */
    protected function linkOAuthAccount(CustomOauthTwoUser $oauthTwoUser, string $provider, User $ownerAccount): void
    {
        $linkedAccount = new UserOAuth();
        $linkedAccount->remote_provider = $provider;
        $linkedAccount->remote_id = $oauthTwoUser->id;
        $linkedAccount->email = $oauthTwoUser->email;
        $linkedAccount->avatar = $oauthTwoUser->avatar;
        $linkedAccount->remote_primary_group = $oauthTwoUser->getRemotePrimaryGroup();
        if ($oauthTwoUserRemoteSecondaryGroups = $oauthTwoUser->getRemoteSecondaryGroups()) {
            $linkedAccount->remote_secondary_groups = implode(',', $oauthTwoUserRemoteSecondaryGroups);
        }
        $linkedAccount->nickname = $oauthTwoUser->getNickname();
        $linkedAccount->name = $oauthTwoUser->getName();
        $linkedAccount->token = $oauthTwoUser->token;
        $linkedAccount->token_expires_at = new Carbon(sprintf('+%d seconds', $oauthTwoUser->expiresIn));
        $linkedAccount->refresh_token = $oauthTwoUser->refreshToken;
        $ownerAccount->linkedAccounts()->save($linkedAccount);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            Auth::logout();
            Event::dispatch(new LoggedOut($user));
        }
        $request->session()->flush();
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json();
        }

        return redirect('/');
    }
}

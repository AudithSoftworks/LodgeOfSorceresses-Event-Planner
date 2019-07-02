<?php namespace App\Http\Controllers;

use App\Events\User\LoggedIn;
use App\Events\User\LoggedOut;
use App\Events\User\Registered;
use App\Exceptions\Common\ValidationException;
use App\Exceptions\Users\LoginNotValidException;
use App\Exceptions\Users\UserNotActivatedException;
use App\Exceptions\Users\UserNotMemberInDiscord;
use App\Extensions\Socialite\CustomOauthTwoUser;
use App\Models\User;
use App\Models\UserOAuth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Factory as SocialiteContract;

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
     * Log the user in.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function loginViaWeb(Request $request): RedirectResponse
    {
        $validator = app('validator')->make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('email', 'password');

        if (app('auth.driver')->attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            event(new LoggedIn($user = app('auth.driver')->user()));

            if ($request->expectsJson()) {
                return response()->json(['data' => $user]);
            }

            return redirect()->intended($this->redirectPath());
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if (!$lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        if ($request->expectsJson()) {
            throw new LoginNotValidException('LoginViaWeb should not expect Json Response!');
        }

        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => app('translator')->get('auth.failed'),
            ]);
    }

    /**
     * @param \Laravel\Socialite\Contracts\Factory $socialite
     * @param string                               $provider
     *
     * @return RedirectResponse
     */
    public function handleOAuthRedirect(SocialiteContract $socialite, $provider): RedirectResponse
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
            if (!empty($oauthTwoUser->token)) {
                $request->session()->put('oauth_provider', $provider);
                $request->session()->put('token', $oauthTwoUser->token);
            }

            return redirect()->intended($this->redirectPath());
        }

        return redirect('/');
    }

    /**
     * @param CustomOauthTwoUser $oauthTwoUser
     * @param string             $provider
     *
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \App\Exceptions\Users\UserNotActivatedException
     * @throws \App\Exceptions\Users\UserNotMemberInDiscord
     */
    protected function loginViaOAuth(CustomOauthTwoUser $oauthTwoUser, string $provider): bool
    {
        if ($provider === 'discord') {
            if (!($discordUser = app('discord.api')->getGuildMember($oauthTwoUser->getId()))) {
                throw new UserNotMemberInDiscord('You need to join Lodge Discord server to continue! Please do so and come back afterwards.');
            }
            $oauthTwoUser->nickname = $discordUser['nick'];
        }
        if ($provider === 'ips' && $ipsUser = app('ips.api')->getUser($oauthTwoUser->getId())) {
            $oauthTwoUser->verified = !(bool)$ipsUser['validating'];
        }
        if (!$oauthTwoUser->isVerified()) {
            throw new UserNotActivatedException('Your Discord/Forums account hasn\'t been activated! Please activate it and come back afterwards.');
        }

        /** @var UserOAuth $owningOAuthAccount */
        if ($owningOAuthAccount = UserOAuth::whereRemoteProvider($provider)->whereRemoteId($oauthTwoUser->id)->first()) {
            $ownerAccount = $owningOAuthAccount->owner;
            $oauthTwoUser->getNickname() && $ownerAccount->name = $oauthTwoUser->getNickname();
            $ownerAccount->save();

            app('auth.driver')->login($ownerAccount);
            event(new LoggedIn($ownerAccount));

            return true;
        }

        return $this->registerViaOAuth($oauthTwoUser, $provider);
    }

    /**
     * @param \App\Extensions\Socialite\CustomOauthTwoUser $oauthTwoUser
     * @param string                                       $provider
     *
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function registerViaOAuth(CustomOauthTwoUser $oauthTwoUser, string $provider): bool
    {
        $authDriver = app('auth.driver');

        if ($authDriver->check()) {
            $ownerAccount = $authDriver->user();
        } else {
            $ownerAccount = User::withTrashed()->whereEmail($oauthTwoUser->email)->first();
            if (!$ownerAccount) {
                $ownerAccount = User::create([
                    'name' => $oauthTwoUser->getNickname(),
                    'email' => $oauthTwoUser->getEmail(),
                    'password' => app('hash')->make(uniqid('', true))
                ]);
                event(new Registered($ownerAccount, $provider));
            }

            # If user account is soft-deleted, restore it.
            $ownerAccount->trashed() && $ownerAccount::restore();

            # Update user name.
            $oauthTwoUser->getNickname() && $ownerAccount->name = $oauthTwoUser->getNickname();
            $ownerAccount->isDirty() && $ownerAccount->save();
        }
        $this->linkOAuthAccount($oauthTwoUser, $provider, $ownerAccount);
        $authDriver->login($ownerAccount, true);
        event(new LoggedIn($ownerAccount));

        return true;
    }

    /**
     * @param \App\Extensions\Socialite\CustomOauthTwoUser $oauthTwoUser
     * @param string                                       $provider
     * @param User                                         $ownerAccount
     */
    protected function linkOAuthAccount(CustomOauthTwoUser $oauthTwoUser, $provider, $ownerAccount): void
    {
        $linkedAccount = new UserOAuth();
        $linkedAccount->remote_provider = $provider;
        $linkedAccount->remote_id = $oauthTwoUser->id;
        $linkedAccount->email = $oauthTwoUser->email;
        $linkedAccount->avatar = $oauthTwoUser->avatar;
        $linkedAccount->remote_primary_group = $oauthTwoUser->getRemotePrimaryGroup();
        $linkedAccount->nickname = $oauthTwoUser->getNickname();
        $linkedAccount->name = $oauthTwoUser->getName();
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
        $guard = app('auth.driver');
        if ($guard->check()) {
            $user = $guard->user();
            $guard->logout();
            app('events')->dispatch(new LoggedOut($user));
        }
        $request->session()->flush();
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json();
        }

        return redirect('/');
    }
}

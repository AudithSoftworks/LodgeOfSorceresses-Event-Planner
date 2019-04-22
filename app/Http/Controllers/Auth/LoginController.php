<?php namespace App\Http\Controllers\Auth;

use App\Events\User\LoggedIn;
use App\Events\User\LoggedInViaOauth;
use App\Events\User\LoggedOut;
use App\Events\User\Registered;
use App\Exceptions\Common\ValidationException;
use App\Exceptions\Users\LoginNotValidException;
use App\Exceptions\Users\LoginViaOauthFailedException;
use App\Extensions\Socialite\IpsUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOAuth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Socialite\Contracts\Factory as SocialiteContract;
use Laravel\Socialite\Two\User as OauthTwoUser;

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
     * Show the application login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm(): View
    {
        return view('auth/login');
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

        /** @var IpsUser $userInfo */
        $userInfo = $socialite->driver($provider)->user();
        if ($this->loginViaOAuth($userInfo, $provider)) {
            if ($userInfo instanceof IpsUser && !empty($userInfo->token)) {
                $request->session()->put('token', $userInfo->token);
            }

            return redirect()->intended($this->redirectPath());
        }

        return redirect('/')->withErrors(trans('passwords.oauth_failed'));
    }

    /**
     * @param \Laravel\Socialite\Two\User $oauthUserData
     * @param string                      $provider
     *
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function loginViaOAuth(OauthTwoUser $oauthUserData, $provider): bool
    {
        /** @var UserOAuth $owningOAuthAccount */
        if ($owningOAuthAccount = UserOAuth::whereRemoteProvider($provider)->whereRemoteId($oauthUserData->id)->first()) {
            $ownerAccount = $owningOAuthAccount->owner;
            if ($provider === 'ips') {
                app('auth.driver')->login($ownerAccount);
                event(new LoggedIn($ownerAccount, $provider));
            }
            event(new LoggedInViaOauth($owningOAuthAccount));

            return true;
        }

        return !$this->registerViaOAuth($oauthUserData, $provider) ? false : true;
    }

    /**
     * @param \Laravel\Socialite\Two\User $oauthUserData
     * @param string                      $provider
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function registerViaOAuth(OauthTwoUser $oauthUserData, $provider)
    {
        /** @var \App\Models\User $ownerAccount */
        if ($provider === 'ips') {
            $ownerAccount = User::withTrashed()->whereEmail($oauthUserData->email)->first();
        } else {
            $ownerAccount = app('auth.driver')->user();
        }

        if (!$ownerAccount) {
            $ownerAccount = User::create([
                'name' => $oauthUserData->name,
                'email' => $oauthUserData->email,
                'password' => app('hash')->make(uniqid('', true))
            ]);
            event(new Registered($ownerAccount, $provider));
        }

        # If user account is soft-deleted, restore it.
        $ownerAccount->trashed() && $ownerAccount::restore();

        # Update missing user name.
        if (!$ownerAccount->name && $oauthUserData->name) {
            $ownerAccount->name = $oauthUserData->name;
            $ownerAccount->save();
        }

        if ($doLinkOAuthAccount = $this->linkOAuthAccount($oauthUserData, $provider, $ownerAccount)) {
            app('auth.driver')->login($ownerAccount, true);
        }

        event(new LoggedIn($ownerAccount, $provider));

        return $doLinkOAuthAccount;
    }

    /**
     * @param \Laravel\Socialite\Two\User|IpsUser $oauthUserData
     * @param string                              $provider
     * @param User                                $ownerAccount
     *
     * @return \App\Models\User|bool
     */
    protected function linkOAuthAccount(OauthTwoUser $oauthUserData, $provider, $ownerAccount)
    {
        $linkedAccount = new UserOAuth();
        $linkedAccount->remote_provider = $provider;
        $linkedAccount->remote_id = $oauthUserData->id;
        $linkedAccount->email = $oauthUserData->email;
        $linkedAccount->avatar = $oauthUserData->avatar;
        property_exists($oauthUserData, 'remotePrimaryGroup') && $linkedAccount->remote_primary_group = $oauthUserData->remotePrimaryGroup;
        property_exists($oauthUserData, 'nickname') && $linkedAccount->nickname = $oauthUserData->nickname;
        property_exists($oauthUserData, 'name') && $linkedAccount->name = $oauthUserData->name;

        if (!$ownerAccount->linkedAccounts()->save($linkedAccount)) {
            throw new LoginViaOauthFailedException();
        }

        event(new LoggedInViaOauth($linkedAccount->refresh()));

        return $ownerAccount;
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
        if (app('auth.driver')->check()) {
            $user = app('auth.driver')->user();

            app('auth.driver')->logout();

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

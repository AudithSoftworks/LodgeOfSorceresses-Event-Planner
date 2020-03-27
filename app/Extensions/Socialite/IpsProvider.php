<?php

namespace App\Extensions\Socialite;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class IpsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $ipsUrl;

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected $scopes = [
        'profile',
        'email',
        'calendar',
        'forum',
        'gallery',
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

        $this->ipsUrl = trim(config('services.ips.url'), '/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->ipsUrl . '/oauth/authorize/', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->ipsUrl . '/oauth/token/';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code): array
    {
        return Arr::add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get($this->ipsUrl . '/api/core/me', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array $user
     *
     * @return User
     */
    protected function mapUserToObject(array $user): User
    {
        return (new CustomOauthTwoUser())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['name'],
            'name' => $user['name'],
            'email' => $user['email'],
            'remotePrimaryGroup' => $user['primaryGroup']['id'],
            'avatar' => $user['photoUrl'],
            'token' => Arr::get($user, 'access_token'),
            'provider' => 'ips',
        ]);
    }
}

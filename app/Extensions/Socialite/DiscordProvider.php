<?php

namespace App\Extensions\Socialite;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DiscordProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $discordUrl;

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected $scopes = [
        'identify',
        'email',
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

        $this->discordUrl = trim(config('services.discord.url'), '/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->discordUrl . '/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->discordUrl . '/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
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
        $response = $this->getHttpClient()->get($this->discordUrl . '/users/@me', [
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
     * @param array $user
     *
     * @return User
     */
    protected function mapUserToObject(array $user): User
    {
        return (new CustomOauthTwoUser())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['username'] . '#' . $user['discriminator'],
            'email' => $user['email'],
            'token' => Arr::get($user, 'access_token'),
            'verified' => $user['verified'],
            'avatar' => $user['avatar'],
            'provider' => 'discord',
        ]);
    }
}

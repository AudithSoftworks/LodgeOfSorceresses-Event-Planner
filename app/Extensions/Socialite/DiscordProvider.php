<?php

namespace App\Extensions\Socialite;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DiscordProvider extends AbstractProvider implements ProviderInterface
{
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
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://discord.com/api/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields($state = null): array
    {
        return array_merge(parent::getCodeFields($state), ['prompt' => 'none']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://discord.com/api/oauth2/token';
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
     *
     * @throws \JsonException
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get('https://discord.com/api/users/@me', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
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

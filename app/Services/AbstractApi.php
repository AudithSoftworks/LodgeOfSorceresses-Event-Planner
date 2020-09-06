<?php

namespace App\Services;

use App\Models\UserOAuth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

abstract class AbstractApi implements ApiInterface
{
    public string $provider;

    protected Client $apiClient;

    protected Client $oauthClient;

    protected string $apiUrl;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function executeCallback(callable $callable, ...$args)
    {
        while (true) {
            try {
                return $callable(...$args);
            } catch (ClientException $e) {
                if (($errorCode = $e->getCode()) === Response::HTTP_NOT_FOUND || $errorCode === Response::HTTP_FORBIDDEN) {
                    break;
                }
                if ($e->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                    $this->waitForRateLimiting($e);
                    continue;
                }
                throw $e;
            } catch (ServerException $e) {
                throw $e;
            }
        }

        return null;
    }

    public function waitForRateLimiting(ClientException $e): void
    {
        $headers = $retryAfterMatch = [];
        $response = $e->getResponse();
        $response !== null && $headers = $response->getHeaders();

        if (isset($headers['X-RateLimit-Remaining']) && $headers['X-RateLimit-Remaining'] === '0') {
            $microSecondsToWait = (int)$headers['Retry-After'] * 1000;
            usleep($microSecondsToWait);

            return;
        }

        if (isset($headers['X-RateLimit-Remaining']) && $headers['X-RateLimit-Remaining'] === '0') {
            $sleepDuration = (int)$headers['X-RateLimit-Reset'] - time();
            sleep($sleepDuration);

            return;
        }

        if (preg_match('/"retry_after": (\d+)/', $e->getMessage(), $retryAfterMatch) !== false) {
            $microSecondsToWait = (int)$retryAfterMatch[1] * 1000;
            usleep($microSecondsToWait);

            return;
        }

        throw $e;
    }

    public function getToken(): ?string
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if ($me !== null) {
            $me->loadMissing(['linkedAccounts']);
            /** @var \App\Models\UserOAuth $oauthAccount */
            $oauthAccount = $me->linkedAccounts()->where('remote_provider', $this->provider)->first();
            if ($oauthAccount !== null) {
                if ($this->hasTokenExpired($oauthAccount)) {
                    $this->refreshToken($oauthAccount);
                }

                return $oauthAccount->token;
            }
        }

        return null;
    }

    private function hasTokenExpired(UserOAuth $oauthAccount): bool
    {
        $now = new Carbon();
        if ($now->isAfter($oauthAccount->token_expires_at)) {
            return true;
        }

        return false;
    }

    abstract protected function refreshToken(UserOAuth $oauthAccount): void;

    protected function createHttpClient(array $options = []): Client
    {
        return new Client(
            array_merge([
                'base_uri' => $this->apiUrl,
            ], $options)
        );
    }

    abstract protected function getApiClient(): Client;

    abstract protected function getOauthClient(): Client;
}

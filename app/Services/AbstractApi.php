<?php

namespace App\Services;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Response;

abstract class AbstractApi implements ApiInterface
{
    public function executeCallback(callable $callable, ...$args)
    {
        while (true) {
            try {
                return $callable(...$args);
            } catch (ClientException $e) {
                if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                    break;
                }
                if ($e->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                    $this->waitForRateLimiting($e);
                    continue;
                }
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
}

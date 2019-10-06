<?php

namespace App\Services;

use GuzzleHttp\Exception\ClientException;

interface ApiInterface
{
    public function executeCallback(callable $callable, ...$args);

    public function waitForRateLimiting(ClientException $e): void;
}

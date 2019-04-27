<?php

namespace App\Services;

use GuzzleHttp\Client;

class PmgApi
{
    private const API_ENDPOINT = 'https://beast.pathfindermediagroup.com/api/eso/';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array
     */
    private $lastResponseHeaders = [];

    public function __construct()
    {
        $botAccessToken = config('services.pmg.api_token');
        $encodedToken = base64_encode($botAccessToken);
        $this->client = new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers' => [
                'Authorization' => 'Basic ' . $encodedToken,
                'Content-Type' => 'application/json'
            ],
        ]);
    }

    public function getLastResponseHeaders(): array
    {
        return $this->lastResponseHeaders;
    }

    public function getAllSets(): array
    {
        $response = $this->client->get('sets');
        $this->lastResponseHeaders = $response->getHeaders();

        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
    }

    public function getAllSkills(): array
    {
        $response = $this->client->get('skills');
        $this->lastResponseHeaders = $response->getHeaders();

        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
    }
}

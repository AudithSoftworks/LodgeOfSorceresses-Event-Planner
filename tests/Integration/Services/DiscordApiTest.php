<?php

namespace App\Tests\Integration\Services;

use App\Services\DiscordApi;
use App\Tests\IlluminateTestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;

class DiscordApiTest extends IlluminateTestCase
{
    /**
     * @var \App\Services\DiscordApi
     */
    private $discordApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->discordApi = new DiscordApi();
    }

    /**
     * @return int[]
     */
    public function testCreateMessageInChannel(): array
    {
        $channelId = env('DISCORD_TEST_CHANNEL_ID');
        $resultOne = $this->discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode(['content' => 'Test 1'])
            ]
        ]);
        $resultTwo = $this->discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode(['content' => 'Test 2'])
            ]
        ]);
        $resultThree = $this->discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode(['content' => 'Test 3'])
            ]
        ]);
        $this->assertIsArray($resultOne);
        $this->assertArrayHasKey('id', $resultOne);
        $this->assertArrayHasKey('content', $resultOne);
        $this->assertEquals('Test 1', $resultOne['content']);
        $this->assertEquals('Test 2', $resultTwo['content']);
        $this->assertArrayHasKey('channel_id', $resultOne);

        return [$resultOne['id'], $resultTwo['id'], $resultThree['id']];
    }

    /**
     * @depends testCreateMessageInChannel
     *
     * @param int[] $messageIds
     *
     * @return int[]
     */
    public function testReactToMessageInChannel(array $messageIds): array
    {
        $channelId = env('DISCORD_TEST_CHANNEL_ID');
        $result = $this->discordApi->reactToMessageInChannel($channelId, $messageIds[0], '✅');
        $this->assertIsBool($result);
        $this->assertTrue($result);

        return $messageIds;
    }

    /**
     * @depends testReactToMessageInChannel
     *
     * @param int[] $messageIds
     */
    public function testDeleteMessageInChannel(array $messageIds): void
    {
        $channelId = env('DISCORD_TEST_CHANNEL_ID');
        $result = $this->discordApi->deleteMessagesInChannel($channelId, [array_shift($messageIds)]);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, $messageIds);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, ['some_bogus_id']);
        $this->assertIsBool($result);
        $this->assertFalse($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, []);
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testGetLastResponseHeaders(): void
    {
        $channelId = env('DISCORD_TEST_CHANNEL_ID');
        try {
            while (true) {
                $this->discordApi->createMessageInChannel($channelId, [
                    RequestOptions::FORM_PARAMS => [
                        'payload_json' => json_encode(['content' => 'Test'])
                    ]
                ]);
            }
        } catch (ClientException $e) {
            $this->assertRegExp('/429 TOO MANY REQUESTS/', $e->getMessage());
            $lastResponseHeaders = $this->discordApi->getLastResponseHeaders();
            $this->assertIsArray($lastResponseHeaders);
            $this->assertNotEmpty($lastResponseHeaders);
            $this->assertArrayHasKey('X-RateLimit-Limit', $lastResponseHeaders);
            $this->assertArrayHasKey('X-RateLimit-Remaining', $lastResponseHeaders);
            $this->assertArrayHasKey('X-RateLimit-Reset', $lastResponseHeaders);
        }
    }

    public function testGetGuildMember(): array
    {
        sleep(1);
        try {
            $memberId = '568032622404567060';
            $result = $this->discordApi->getGuildMember($memberId);
        } catch (ClientException $e) {
            $this->assertRegExp('/429 TOO MANY REQUESTS/', $e->getMessage());

            return [];
        }

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertIsArray($result['user']);
        $this->assertArrayHasKey('roles', $result);
        $this->assertIsArray($result['roles']);

        return $result;
    }

    /**
     * @depends testGetGuildMember
     *
     * @param array $member
     */
    public function testModifyGuildMember(array $member): void
    {
        sleep(1);
        try {
            $result = $this->discordApi->modifyGuildMember($member['user']['id'], [
                'roles' => [DiscordApi::ROLE_MEMBERS, DiscordApi::ROLE_INITIATE]
            ]);
            $this->assertTrue(true, $result);
        } catch (ClientException $e) {
            $this->assertRegExp('/429 TOO MANY REQUESTS/', $e->getMessage());
        }
    }

    /**
     * @depends testGetGuildMember
     *
     * @param array $member
     */
    public function testCreateDmChannel(array $member): void
    {
        sleep(1);

        $result = $this->discordApi->createDmChannel($member['user']['id']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals($result['type'], '1');
    }

    public function testGetGuildRoles(): void
    {
        sleep(1);

        $result = $this->discordApi->getGuildRoles();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('hoist', $result[0]);
        $this->assertArrayHasKey('mentionable', $result[0]);
    }
}

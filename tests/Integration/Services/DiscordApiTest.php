<?php

namespace App\Tests\Integration\Services;

use App\Services\DiscordApi;
use App\Tests\IlluminateTestCase;
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
        $channelId = config('services.discord.channels.officer_hq');
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
        $channelId = config('services.discord.channels.officer_hq');
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
        $channelId = config('services.discord.channels.officer_hq');
        $result = $this->discordApi->deleteMessagesInChannel($channelId, [array_shift($messageIds)]);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, $messageIds);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, ['2121212']); // some bogus id
        $this->assertIsBool($result);
        $this->assertFalse($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, []);
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testGetGuildMember(): array
    {
        $memberId = '568032622404567060';
        $result = $this->discordApi->getGuildMember($memberId);

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
        $result = $this->discordApi->modifyGuildMember($member['user']['id'], [
            'roles' => [DiscordApi::ROLE_MEMBERS, DiscordApi::ROLE_INITIATE]
        ]);
        $this->assertTrue(true, $result);
    }

    /**
     * @depends testGetGuildMember
     *
     * @param array $member
     */
    public function testCreateDmChannel(array $member): void
    {
        $result = $this->discordApi->createDmChannel($member['user']['id']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals($result['type'], '1');
    }

    public function testGetGuildRoles(): void
    {
        $result = $this->discordApi->getGuildRoles();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('hoist', $result[0]);
        $this->assertArrayHasKey('mentionable', $result[0]);
    }
}

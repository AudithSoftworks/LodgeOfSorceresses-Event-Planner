<?php

namespace App\Tests\Integration\Services;

use App\Services\DiscordApi;
use App\Tests\IlluminateTestCase;
use GuzzleHttp\RequestOptions;

class DiscordApiTest extends IlluminateTestCase
{
    private DiscordApi $discordApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->discordApi = new DiscordApi();
    }

    /**
     * @throws \JsonException
     *
     * @return int[]
     */
    public function testCreateMessageInChannel(): array
    {
        $channelId = config('services.discord.channels.officer_hq');
        $resultOne = $this->discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode(['content' => 'Test 1'], JSON_THROW_ON_ERROR)
            ]
        ]);
        $resultTwo = $this->discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode(['content' => 'Test 2'], JSON_THROW_ON_ERROR)
            ]
        ]);
        $resultThree = $this->discordApi->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode(['content' => 'Test 3'], JSON_THROW_ON_ERROR)
            ]
        ]);
        static::assertIsArray($resultOne);
        static::assertArrayHasKey('id', $resultOne);
        static::assertArrayHasKey('content', $resultOne);
        static::assertEquals('Test 1', $resultOne['content']);
        static::assertEquals('Test 2', $resultTwo['content']);
        static::assertArrayHasKey('channel_id', $resultOne);

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
        static::assertIsBool($result);
        static::assertTrue($result);

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
        static::assertIsBool($result);
        static::assertTrue($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, $messageIds);
        static::assertIsBool($result);
        static::assertTrue($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, ['2121212']); // some bogus id
        static::assertIsBool($result);
        static::assertFalse($result);

        $result = $this->discordApi->deleteMessagesInChannel($channelId, []);
        static::assertIsBool($result);
        static::assertFalse($result);
    }

    public function testGetGuildMember(): array
    {
        $memberId = '568032622404567060';
        $result = $this->discordApi->getGuildMember($memberId);

        static::assertIsArray($result);
        static::assertArrayHasKey('user', $result);
        static::assertIsArray($result['user']);
        static::assertArrayHasKey('roles', $result);
        static::assertIsArray($result['roles']);

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
        static::assertTrue(true, $result);
    }

    /**
     * @depends testGetGuildMember
     *
     * @param array $member
     */
    public function testCreateDmChannel(array $member): void
    {
        $result = $this->discordApi->createDmChannel($member['user']['id']);

        static::assertIsArray($result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('type', $result);
        static::assertEquals('1', $result['type']);
    }

    public function testGetGuildRoles(): void
    {
        $result = $this->discordApi->getGuildRoles();

        static::assertIsArray($result);
        static::assertNotEmpty($result);
        static::assertArrayHasKey('id', $result[0]);
        static::assertArrayHasKey('hoist', $result[0]);
        static::assertArrayHasKey('mentionable', $result[0]);
    }
}

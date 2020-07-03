<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class EmptyDiscordTestingChannel extends Command
{
    /**
     * @var string
     */
    protected $signature = 'discord:testing:clear';

    /**
     * @var string
     */
    protected $description = 'Empties Discord test channel.';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->error('This command can\'t be run on Production environment!');

            return;
        }
        $testChannelId = config('services.discord.channels.testing');
        $discordApi = app('discord.api');
        while(count($messages = $discordApi->getChannelMessages($testChannelId))) {
            $messageIds = [];
            foreach ($messages as $message) {
                $timestamp = new CarbonImmutable($message['timestamp']);
                if ($timestamp->isAfter(new CarbonImmutable('2 weeks ago'))) {
                    $messageIds[] = $message['id'];
                } elseif (empty($messageIds)) {
                    $this->warn('Reached the end of deleteable messages...');
                    $response = $discordApi->deleteMessagesInChannel($testChannelId, [$message['id']]);
                    $response && $this->info('Successfully deleted a single message.');
                }
            }
            $response = $discordApi->deleteMessagesInChannel($testChannelId, $messageIds);
            $response && $this->info(sprintf('Successfully deleted %d messages.', count($messageIds)));
        }

        $this->info('Done.');
    }
}

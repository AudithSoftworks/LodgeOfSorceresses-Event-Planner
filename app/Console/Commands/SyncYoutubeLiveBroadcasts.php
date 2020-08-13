<?php

namespace App\Console\Commands;

use App\Models\YoutubeFeedsChannel;
use App\Models\YoutubeFeedsVideo;
use Carbon\CarbonImmutable;
use Google_Client;
use Google_Service_Exception;
use Google_Service_YouTube;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;

class SyncYoutubeLiveBroadcasts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'youtube:live {channelId? : 24-letter channel ID, e.g. UCuLGCNYH1t5DyQQ5tfU4Hdw}';

    /**
     * @var string
     */
    protected $description = 'Posts live Youtube broadcasts to Discord server.';

    private Google_Service_YouTube $service;

    public function __construct()
    {
        parent::__construct();
        $client = (new Google_Client());
        $client->setApplicationName('Lodge of Sorceresses');
        $apiKey = config('services.google.youtube_data_api_key');
        $client->setDeveloperKey($apiKey);
        $this->service = new Google_Service_YouTube($client);
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $channelId = $this->argument('channelId');
        $channelIds = $channelId ? [$channelId] : array_column(YoutubeFeedsChannel::query()->get(['id'])->toArray(), 'id');
        foreach ($channelIds as $channelId) {
            $queryParams = [
                'channelId' => $channelId,
                'maxResults' => 1,
                'eventType' => 'live',
                'type' => 'video',
            ];
            try {
                $response = $this->service->search->listSearch('snippet', $queryParams);
            } catch (Google_Service_Exception $e) {
                $message = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);
                $this->error(sprintf('GoogleApi Error (code %d): %s', $e->getCode(), strip_tags($message['error']['message'])));

                return;
            }
            /** @var \Google_Service_YouTube_SearchResult[] $items */
            $items = $response->getItems();
            if (count($items) > 0) {
                $channel = YoutubeFeedsChannel::query()->find($channelId);
                $channelUpdatedAt = $channel->updated_at ? clone $channel->updated_at : new CarbonImmutable('1 weeks ago');
                foreach ($items as $item) {
                    $id = $item->getId();
                    $snippet = $item->getSnippet();

                    $thumbnails = $snippet->getThumbnails();
                    YoutubeFeedsVideo::unguard(true);
                    $video = YoutubeFeedsVideo::query()->firstOrNew([
                        'id' => $id->getVideoId(),
                    ], [
                        'channel_id' => $snippet->getChannelId(),
                        'title' => htmlspecialchars_decode($snippet->getTitle(), ENT_QUOTES),
                        'description' => htmlspecialchars_decode($snippet->getDescription(), ENT_QUOTES),
                        'url' => sprintf('https://www.youtube.com/watch?v=%s', $id->getVideoId()),
                        'thumbnail' => $thumbnails->getHigh()->getUrl(),
                        'created_at' => new CarbonImmutable($snippet->getPublishedAt()),
                        'updated_at' => new CarbonImmutable(),
                    ]);
                    YoutubeFeedsVideo::unguard(false);
                    $this->postOnDiscord($video);
                }
                $this->info(sprintf('Checked channel %s (%s) for ongoing live-streams.', $channel->title, $channel->id));
            }
        }

        $this->info('Done.');
    }

    /**
     * @param \App\Models\YoutubeFeedsVideo $video
     *
     * @throws \JsonException
     */
    private function postOnDiscord(YoutubeFeedsVideo $video): void
    {
        $subscriptionsChannelId = config('services.discord.channels.subscriptions');
        $discordApi = app('discord.api');
        $responseDecoded = $discordApi->createMessageInChannel($subscriptionsChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => 'Live stream from **' . $video->channel->title . '**: ' . $video->title . ".\n"
                        . $video->url,
                    'tts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
        ]);
        $video->discord_message_id = $responseDecoded['id'];
        $this->info('Posted on Discord (Video ID: ' . $video->id . ')');
    }
}

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

class SyncYoutubeRssFeeds extends Command
{
    /**
     * @var string
     */
    protected $signature = 'youtube:sync {channelId? : 24-letter channel ID, e.g. UCuLGCNYH1t5DyQQ5tfU4Hdw}';

    /**
     * @var string
     */
    protected $description = 'Syncs Youtube RSS feeds into a Discord channel.';

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
                'maxResults' => 50,
                'order' => 'date',
                'type' => 'video',
            ];
            try {
                $response = $this->service->search->listSearch('snippet', $queryParams);
            } catch (Google_Service_Exception $e) {
                $message = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);
                $this->error(sprintf('GoogleApi Error (code %d): %s', $e->getCode(), strip_tags($message['error']['message'])));

                return;
            }
            /** @var \Google_Service_YouTube_SearchResult[] $searchResultItems */
            $searchResultItems = $response->getItems();
            if (count($searchResultItems) > 0) {
                $channel = YoutubeFeedsChannel::query()->find($channelId);
                $channelUpdatedAt = $channel->updated_at ? clone $channel->updated_at : new CarbonImmutable('1 weeks ago');

                $resourceIdObjList = array_column($searchResultItems, 'id');
                $videoIdList = array_column($resourceIdObjList, 'videoId');

                $queryParams = [
                    'id' => implode(',', $videoIdList),
                ];
                $videoListResponse = $this->service->videos->listVideos([
                    'contentDetails',
                    'id',
                    'liveStreamingDetails',
                    'player',
                    'snippet',
                    'statistics',
                    'status',
                    'topicDetails',
                ], $queryParams);
                /** @var \Google_Service_YouTube_Video[] $items */
                $items = $videoListResponse->getItems();
                foreach ($items as $item) {
                    $id = $item->getId();
                    $snippet = $item->getSnippet();
                    // Skip 'live' streams or 'upcoming' videos
                    // @see https://developers.google.com/youtube/v3/docs/videos
                    if ($snippet->getLiveBroadcastContent() !== 'none') {
                        continue;
                    }

                    $thumbnails = $snippet->getThumbnails();
                    YoutubeFeedsVideo::unguard(true);
                    $video = YoutubeFeedsVideo::query()->updateOrCreate([
                        'id' => $id,
                    ], [
                        'channel_id' => $snippet->getChannelId(),
                        'title' => htmlspecialchars_decode($snippet->getTitle(), ENT_QUOTES),
                        'description' => htmlspecialchars_decode($snippet->getDescription(), ENT_QUOTES),
                        'url' => sprintf('https://www.youtube.com/watch?v=%s', $id),
                        'thumbnail' => $thumbnails->getHigh()->getUrl(),
                        'created_at' => new CarbonImmutable($snippet->getPublishedAt()),
                        'updated_at' => new CarbonImmutable(),
                    ]);
                    YoutubeFeedsVideo::unguard(false);
                    if ($video->wasRecentlyCreated && $video->created_at->isAfter($channelUpdatedAt)) {
                        $this->info(sprintf('Processing video (video id: %s)', $id));
                        $this->postOnDiscord($video);
                        $this->postOnIps($video, $item);
                    } else {
                        $this->warn(sprintf('Skipping video (video id: %s) - already recorded or too old', $video->id));
                        continue;
                    }
                    $video->isDirty() && $video->save();

                    $video->created_at->isAfter($channel->updated_at) && $channel->updated_at = $video->created_at;
                }
                $channel->isDirty() && $channel->save();
                $this->info(sprintf('Synced channel %s (%s).', $channel->title, $channel->id));
            }
        }

        $this->info('Done.');
    }

    /**
     * @param \App\Models\YoutubeFeedsVideo $videoModel
     *
     * @throws \JsonException
     */
    private function postOnDiscord(YoutubeFeedsVideo $videoModel): void
    {
        $subscriptionsChannelId = config('services.discord.channels.subscriptions');
        $discordApi = app('discord.api');
        $responseDecoded = $discordApi->createMessageInChannel($subscriptionsChannelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => 'New video from **' . $videoModel->channel->title . '**: ' . $videoModel->title . ".\n"
                        . $videoModel->url,
                    'tts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
        ]);
        $videoModel->discord_message_id = $responseDecoded['id'];
        $this->info('Posted on Discord (Video ID: ' . $videoModel->id . ')');
    }

    private function postOnIps(YoutubeFeedsVideo $videoModel, \Google_Service_YouTube_Video $videoObj): void
    {
        if (!app()->environment('production')) {
            return;
        }
        $forumId = config('services.ips.forums.herald');
        $title = '[' . $videoModel->channel->title . '] ' . $videoModel->title;
        $post = sprintf('
            <div class="ipsEmbeddedVideo" contenteditable="false">
                <div>%s</div>
            </div>
        ', $videoObj->getPlayer()->getEmbedHtml());
        $ipsApi = app('ips.api');
        $ipsApi->createTopic($forumId, $title, $post);
        $this->info('Posted on Forums (Video ID: ' . $videoModel->id . ')');
    }
}

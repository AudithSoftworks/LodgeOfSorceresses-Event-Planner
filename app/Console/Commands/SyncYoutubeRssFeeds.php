<?php

namespace App\Console\Commands;

use App\Models\YoutubeFeedsChannel;
use App\Models\YoutubeFeedsVideo;
use Carbon\CarbonImmutable;
use Cloudinary;
use Google_Client;
use Google_Service_Exception;
use Google_Service_YouTube;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;

class SyncYoutubeRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:rss {channelId? : 24-letter channel ID, e.g. UCuLGCNYH1t5DyQQ5tfU4Hdw}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs Youtube RSS feeds into a Discord channel.';

    private Google_Service_YouTube $service;

    public function __construct()
    {
        parent::__construct();
        Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
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
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => '120',
            ],
        ];
        $context = stream_context_create($options);
        libxml_set_streams_context($context);

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
            /** @var \Google_Service_YouTube_SearchResult[] $items */
            $items = $response->getItems();
            if (count($items) > 0) {
                $channel = YoutubeFeedsChannel::query()->find($channelId);
                foreach ($items as $item) {
                    $id = $item->getId();
                    $snippet = $item->getSnippet();

                    $thumbnails = $snippet->getThumbnails();
                    YoutubeFeedsVideo::unguard(true);
                    $video = YoutubeFeedsVideo::query()->updateOrCreate([
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
                    if ($video->wasRecentlyCreated && $video->created_at->isAfter($channel->updated_at)) {
                        $this->info(sprintf('Processing video (video id: %s)', $video->id));
                        $this->postOnDiscord($video);
                        $this->postOnIps($video);
                    } else {
                        $this->warn(sprintf('Skipping video (video id: %s)', $video->id));
                        continue;
                    }
                    $video->isDirty() && $video->save();

                    $channel->updated_at = $video->updated_at;
                }
                $channel->isDirty() && $channel->save();
                $this->info(sprintf('Synced channel %s (%s).', $channel->title, $channel->id));
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
                    'content' => 'New video from **' . $video->channel->title . '**: ' . $video->title . ".\n"
                        . $video->url,
                    'tts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
        ]);
        $video->discord_message_id = $responseDecoded['id'];
        $this->info('Posted on Discord (Video ID: ' . $video->id . ')');
    }

    private function postOnIps(YoutubeFeedsVideo $video): void
    {
        $forumId = config('services.ips.forums.herald');
        $title = '[' . $video->channel->title . '] ' . $video->title;
        $post = '
            <div class="ipsEmbeddedVideo" contenteditable="false">
                <div>
                    <iframe allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen="" height="270" id="ips_uid_1588_7" width="480" frameborder="0" data-embed-src="https://www.youtube.com/embed/' . $video->id . '?feature=oembed"></iframe>
                </div>
            </div>
        ';
        $ipsApi = app('ips.api');
        $ipsApi->createTopic($forumId, $title, $post);
        $this->info('Posted on Forums (Video ID: ' . $video->id . ')');
    }
}

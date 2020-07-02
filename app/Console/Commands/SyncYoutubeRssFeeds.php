<?php

namespace App\Console\Commands;

use App\Models\YoutubeFeedsChannel;
use App\Models\YoutubeFeedsVideo;
use Carbon\CarbonImmutable;
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

    public function __construct()
    {
        parent::__construct();
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => '120'
            ]
        ];
        $context = stream_context_create($options);
        libxml_set_streams_context($context);

        $channelId = $this->argument('channelId');
        $channelIds = $channelId ? [$channelId] : array_column(YoutubeFeedsChannel::get(['id'])->toArray(), 'id');
        foreach ($channelIds as $channelId) {
            $feedUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channelId;
            $doc = new \DOMDocument();
            $doc->load($feedUrl);

            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
            $xpath->registerNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
            $xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');

            $entries = [];
            foreach ($xpath->query('/atom:feed/atom:entry') as $entry) {
                $entries[] = $entry;
            }
            $entry = end($entries);
            while ($entry) {
                $mediaGroup = $xpath->query('./media:group', $entry)->item(0);

                $videoId = $xpath->query('./yt:videoId', $entry)->item(0)->nodeValue;
                $createdAt = new CarbonImmutable($xpath->query('./atom:published', $entry)->item(0)->nodeValue);
                $channel = YoutubeFeedsChannel::find($channelId);
                if (!$createdAt->addDays(5)->isAfter(new CarbonImmutable())) {
                    $this->warn('Skipped video (ID: ' . $videoId . ') - It is old!');
                    $entry = prev($entries);
                    continue;
                }
                if ($channel->updated_at && $channel->updated_at->isAfter($createdAt)) {
                    $this->warn('Skipped video (ID: ' . $videoId . ') - It is old!');
                    $entry = prev($entries);
                    continue;
                }
                if (YoutubeFeedsVideo::query()->find($videoId)) {
                    $this->warn('Skipped video (ID: ' . $videoId . ') - It is already posted!');
                    $entry = prev($entries);
                    continue;
                }

                $video = new YoutubeFeedsVideo();
                $video->id = $videoId;
                $video->channel_id = $channelId;
                $video->created_at = $createdAt;
                $video->updated_at = new CarbonImmutable($xpath->query('./atom:updated', $entry)->item(0)->nodeValue);
                $video->title = $xpath->query('./media:title', $mediaGroup)->item(0)->nodeValue;
                $video->description = $xpath->query('./media:description', $mediaGroup)->item(0)->nodeValue;
                $video->url = $xpath->query('./atom:link', $entry)->item(0)->attributes->getNamedItem('href')->nodeValue;
                $video->thumbnail = $xpath->query('./media:thumbnail', $mediaGroup)->item(0)->attributes->getNamedItem('url')->nodeValue;

                $this->postOnDiscord($video);
                $this->postOnIps($video);
                sleep(1);

                $video->save();

                $channel->updated_at = $createdAt;
                $channel->isDirty() && $channel->save();

                $entry = prev($entries);
            }
        }

        $this->info('Done.');
    }

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
                ]),
            ]
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

<?php

namespace App\Console\Commands;

use App\Models\YoutubeFeedsChannel;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AddYoutubeRssFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:add {channelId : 24-letter channel ID, e.g. UCuLGCNYH1t5DyQQ5tfU4Hdw}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a new Youtube RSS feed to our RSS-sync list.';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $channelId = $this->argument('channelId');
        $feedUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channelId;
        if (!($xml = file_get_contents($feedUrl))) {
            $this->error('Channel (ID: ' . $channelId . ') is not a valid Youtube channel!');

            return;
        }

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xpath->registerNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
        $xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');

        $channel = new YoutubeFeedsChannel();
        $channel->id = $xpath->query('/atom:feed/yt:channelId')->item(0)->nodeValue;
        $channel->url = $xpath->query('/atom:feed/atom:link[attribute::rel="alternate"]')->item(0)->attributes->getNamedItem('href')->nodeValue;
        $channel->title = $xpath->query('/atom:feed/atom:title')->item(0)->nodeValue;
        $channel->created_at = new CarbonImmutable($xpath->query('/atom:feed/atom:published')->item(0)->nodeValue);
        $channel->updated_at = null;
        $channel->save();

        $this->info('Channel (ID: ' . $channelId . ') added.');

        \Artisan::call('discord:rss', [
            'channelId' => $channelId
        ]);

        $this->info('Channel (ID: ' . $channelId . ') synced.');
    }
}

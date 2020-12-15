<?php

namespace App\Console\Commands;

use App\Models\YoutubeFeedsChannel;
use Carbon\CarbonImmutable;
use Google_Client;
use Google_Service_Exception;
use Google_Service_YouTube;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
        $queryParams = [
            'id' => $channelId,
        ];
        try {
            $response = $this->service->channels->listChannels(['snippet', 'contentDetails', 'status'], $queryParams);
        } catch (Google_Service_Exception $e) {
            $message = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);
            $this->error(sprintf('GoogleApi Error (code %d): %s', $e->getCode(), strip_tags($message['error']['message'])));

            return;
        }
        /** @var \Google_Service_YouTube_Channel[] $items */
        $items = $response->getItems();
        if (!count($items)) {
            $this->error('Channel not found!');

            return;
        }
        $channelData = $items[0];
        $id = $channelData->getId();
        $snippet = $channelData->getSnippet();
        $thumbnails = $snippet->getThumbnails();
        YoutubeFeedsChannel::unguard(true);
        $channel = YoutubeFeedsChannel::query()->updateOrCreate([
            'id' => $channelData->getId(),
        ], [
            'title' => htmlspecialchars_decode($snippet->getTitle(), ENT_QUOTES),
            'url' => sprintf('https://youtube.com/channel/%s', $id),
            'thumbnail' => $thumbnails->getHigh()->getUrl(),
            'created_at' => new CarbonImmutable($snippet->getPublishedAt()),
            'updated_at' => new CarbonImmutable('1 week ago'),
        ]);
        YoutubeFeedsChannel::unguard(false);
        if ($channel->wasRecentlyCreated) {
            $this->info(sprintf('Successfully added channel (channel id: %s)', $channel->id));
        } else {
            $this->warn(sprintf('Channel already in records (channel id: %s)', $channel->id));

            return;
        }

        Artisan::call('youtube:sync', [
            'channelId' => $channelId
        ], $this->getOutput());
    }
}

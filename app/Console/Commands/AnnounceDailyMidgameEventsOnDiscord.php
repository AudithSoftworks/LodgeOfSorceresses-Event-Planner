<?php

namespace App\Console\Commands;

use App\Models\Content;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use PathfinderMediaGroup\ApiLibrary\Api\PledgesApi;
use PathfinderMediaGroup\ApiLibrary\Auth\TokenAuth;

class AnnounceDailyMidgameEventsOnDiscord extends Command
{
    /**
     * @var string
     */
    protected $signature = 'discord:midgame';

    /**
     * @var string
     */
    protected $description = 'Announces daily Midgame events on Discord.';

    /**
     * @throws \JsonException
     * @throws \PathfinderMediaGroup\ApiLibrary\Exception\FailedPmgRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        $botAccessToken = config('services.pmg.api_token');
        $tokenAuthObj = new TokenAuth($botAccessToken);
        $api = new PledgesApi($tokenAuthObj);
        $response = $api->get();

        $locale = App::getLocale();
        if (!in_array($locale, ['en', 'de', 'fr'])) {
            $this->error('Current locale isn\'t supported by PMG API!');

            return;
        }
        $pledges = $response[$locale];
        $guideUrls = $response['url'];

        /** @var Content[] $contents */
        $contents = Content::query()->where('name', 'LIKE', '%' . $pledges[3])->get();
        if ($contents === null || ($contents instanceof Collection && $contents->count() === 0)) {
            $this->error('No content match found!');

            return;
        }

        $this->announceEventInLfmChannelOnDiscord($contents, $guideUrls[3]);

        $this->info('Daily Midgame content was successfully announced on Discord.');
    }

    /**
     * @param \App\Models\Content[] $contents
     * @param string $guideUrl
     *
     * @throws \JsonException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function announceEventInLfmChannelOnDiscord(iterable $contents, string $guideUrl): void
    {
        $contentDescriptions = [];
        foreach ($contents as $content) {
            $contentDescriptions[] = sprintf(
                '%s %s (Tier-%d)',
                $content->name,
                $content->version,
                $content->tier
            );
        }

        $guideUrlParsed = parse_url($guideUrl);

        $guideUrlPageImage = $this->getOgImageFromGuidePage($guideUrl);

        $discordApi = app('discord.api');
        $lfmChannelId = config('services.discord.channels.looking_for');
        $message = [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => 'Guten morgen, @everyone! :wink:'
                        . PHP_EOL . 'For today\'s Midgame (DLC Dungeon) event, please emote here with :shield: , :ambulance: , :crossed_swords: or :bow_and_arrow: indicating your all available roles.'
                        . PHP_EOL . '**Please make sure you are online 5 minutes before event time! Don\'t signup if not sure.**',
                    'tts' => true,
                    'embed' => [
                        'color' => 0x00aa00,
                        'thumbnail' => [
                            'url' => cloudinary_url('special/logo.png', [
                                'secure' => true,
                                'width' => 300,
                                'height' => 300,
                            ]),
                        ],
                        'fields' => [
                            [
                                'name' => 'Event(s)',
                                'value' => implode(PHP_EOL, $contentDescriptions),
                            ],
                            [
                                'name' => 'Time',
                                'value' => 'Between 19:00 and 20:00 CE(S)T',
                            ],
                            [
                                'name' => 'Guides',
                                'value' => sprintf('Read at [%s](%s)', $guideUrlParsed['host'], $guideUrl),
                            ],
                        ],
                        'image' => [
                            'url' => $guideUrlPageImage,
                        ],
                        'footer' => [
                            'text' => 'Sent via Lodge of Sorceresses Guild Planner at: https://planner.lodgeofsorceresses.com',
                        ],
                    ],
                ], JSON_THROW_ON_ERROR),
            ],
        ];
        $discordApi->createMessageInChannel($lfmChannelId, $message);
    }

    /**
     * @param string $url
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return null|string
     */
    private function getOgImageFromGuidePage(string $url): ?string
    {
        $client = new Client([
            'timeout' => 5.0,
        ]);
        $response = $client->request('GET', $url);
        if ($response->getStatusCode() === 200) {
            $body = $response->getBody()->getContents();
            preg_match('#' . preg_quote('<meta property="og:image" content="', '#') . '([^"]*?)' . '"\s*/>#i', $body, $matches);

            return $matches[1];
        }

        return null;
    }
}

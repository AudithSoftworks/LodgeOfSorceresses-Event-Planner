<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use App\Models\UserOAuth;
use Carbon\CarbonImmutable;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TrackAttendances extends Command
{
    private const DLC_DUNGEONS_ALBUM_ID = 1;

    private const TEMPLATE_FOR_MENTION_LINKS_IN_FORUMS = '<a contenteditable="false" data-ipshover="" data-ipshover-target="%s" data-mentionid="%s" href="%s">%s</a>';

    private const TEMPLATE_FOR_MENTION_LINKS_IN_PLANNER = '<a href="/users/%s">%s</a>';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:track';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tracks posts in #achievements Discord channel for Attendance markers.';

    private string $botId;

    private ?string $overallLastMessageIdProcessed;

    private ?string $currentLastMessageIdProcessed = null;

    /**
     * @var array
     */
    private array $ipsOauthNotFoundList = [];

    public function __construct()
    {
        parent::__construct();
        $this->botId = config('services.discord.bot_id');
        $this->overallLastMessageIdProcessed = Cache::get('console-markers-guild:attendance:track');
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $achievementsChannelId = config('services.discord.channels.achievements');
        $lastMessage = null;
        while (true) {
            $this->info('Fetching new batch of messages...');
            $messages = app('discord.api')->getChannelMessages(
                $achievementsChannelId,
                $lastMessage ? ['before' => $lastMessage['id']] : []
            );
            $this->currentLastMessageIdProcessed === null && $this->currentLastMessageIdProcessed = $messages[0]['id'];
            foreach ($messages as $message) {
                if ($this->overallLastMessageIdProcessed !== null && $this->overallLastMessageIdProcessed === $message['id']) {
                    break 2;
                }

                $lastMessage = $message;

                if (empty($message['mentions'])) {
                    continue;
                }
                $mentionedRemoteUserIds = array_column($message['mentions'], 'id');
                if (empty($message['attachments']) || !in_array($this->botId, $mentionedRemoteUserIds, true)) {
                    continue;
                }

                $user = $this->getUserForGivenDiscordRemoteId($message['author']['id']);
                $message['author'] = $user;
                if ($user === null) {
                    continue;
                }

                /** @var UserOAuth $userIpsOauth */
                $userIpsOauth = $user->linkedAccounts()->where('remote_provider', 'ips')->first();
                if ($userIpsOauth === null) {
                    continue;
                }

                $this->info(sprintf('Parsing Attendance report: "%s"...', $message['content']));

                $message = $this->cleanMessage($message);
                $this->notifyUsersWithoutIpsOauthAccount();
                $attendance = $this->persistAttendance($message);
                if ($attendance !== null) {
                    $this->uploadAttachmentToGallery($message, $userIpsOauth, $attendance);
                    $this->info(sprintf('Parsed and Saved text for forums: "%s"...', $message['content_for_forums']));
                    $this->info(sprintf('Parsed and Saved text for Planner: "%s"...', $message['content_for_planner']));
                } else {
                    $this->warn('Already processed, skipping...');
                }
            }
            if (count($messages) < 100) {
                break;
            }
        }
        $this->currentLastMessageIdProcessed !== null && Cache::put('console-markers-guild:attendance:track', $this->currentLastMessageIdProcessed);
        $this->info('Completed.');
    }

    /**
     * @param array $message
     *
     * @return array
     * @throws \Exception
     */
    private function cleanMessage(array $message): array
    {
        # Fetch mentioned IPS OAuth accounts
        $botMentionIndex = null;
        $message['content_for_forums'] = $message['content_for_planner'] = $message['content'];
        foreach ($message['mentions'] as $key => &$mention) {
            if ($mention['id'] === $this->botId) {
                $botMentionIndex = $key;
                # Replace bot mention from the message content
                $message['content_for_forums'] = preg_replace('/<@!' . $this->botId . '>/', '', $message['content_for_forums']);
                $message['content_for_planner'] = preg_replace('/<@!' . $this->botId . '>/', '', $message['content_for_planner']);
                continue;
            }
            $mentionedUser = $this->getUserForGivenDiscordRemoteId($mention['id']);
            if ($mentionedUser !== null) {
                /** @var UserOAuth $ipsAccountOfMentionedUser */
                $ipsAccountOfMentionedUser = $mentionedUser->linkedAccounts()->where('remote_provider', 'ips')->first();
                if ($ipsAccountOfMentionedUser !== null) {
                    $slug = $ipsAccountOfMentionedUser->remote_id . '-member';
                    $formattedMentionForForumsContent = sprintf(
                        self::TEMPLATE_FOR_MENTION_LINKS_IN_FORUMS,
                        '<___base_url___>/profile/' . $slug . '/?do=hovercard',
                        $ipsAccountOfMentionedUser->remote_id,
                        '<___base_url___>/profile/' . $slug . '/',
                        $ipsAccountOfMentionedUser->name
                    );
                    $formattedMentionForPlannerContent = sprintf(
                        self::TEMPLATE_FOR_MENTION_LINKS_IN_PLANNER,
                        $mentionedUser->id,
                        $mentionedUser->name
                    );
                    $message['content_for_forums'] = preg_replace('/<@!' . $mention['id'] . '>/', $formattedMentionForForumsContent, $message['content_for_forums']);
                    $message['content_for_planner'] = preg_replace('/<@!' . $mention['id'] . '>/', $formattedMentionForPlannerContent, $message['content_for_planner']);
                    $mention = $mentionedUser;
                } else {
                    if (!in_array($mention['id'], $this->ipsOauthNotFoundList, true)) {
                        $message['content_for_forums'] = preg_replace('/<@!' . $mention['id'] . '>/', '', $message['content_for_forums']);
                        $message['content_for_planner'] = preg_replace('/<@!' . $mention['id'] . '>/', '', $message['content_for_planner']);
                        $this->ipsOauthNotFoundList[] = $mention['id'];
                    }
                    $this->warn('No IPS account for user: ' . $mention['username']);
                }
            }
        }
        unset($mention);
        if ($botMentionIndex !== null) {
            unset($message['mentions'][$botMentionIndex]);
        }

        # Remove mentions not allowed by Discord
        $message['content_for_forums'] = preg_replace('/<?@[\S]+/', ' ', $message['content_for_forums']);
        $message['content_for_planner'] = preg_replace('/<?@[\S]+/', ' ', $message['content_for_planner']);

        $message['timestamp'] = new CarbonImmutable($message['timestamp']);

        return $message;
    }

    public function persistAttendance(array $message): ?Attendance
    {
        $attendance = Attendance::query()->where('discord_message_id', '=', $message['id'])->get()->first();
        if ($attendance !== null) {
            return null;
        }

        $attendance = new Attendance();
        $attendance->text = $message['content'];
        $attendance->text_for_forums = $message['content_for_forums'];
        $attendance->text_for_planner = $message['content_for_planner'];
        $attendance->discord_message_id = $message['id'];
        $attendance->created_at = $message['timestamp'];
        $attendance->isDirty() && $attendance->save();

        $collectionOfMentions = collect($message['mentions']);
        $collectionOfMentions = $collectionOfMentions->reject(static function ($mention) {
            return !($mention instanceof User);
        });
        $attendance->attendees()->sync($collectionOfMentions->pluck('id'));
        $attendance->save();

        if ($message['author'] instanceof User) {
            $attendance->attendees()->save($message['author'], ['is_author' => true]);
        }

        return $attendance;
    }

    private function uploadAttachmentToGallery(array $message, UserOAuth $userIpsOauth, Attendance $attendance): array
    {
        $gallery_image_ids = [];
        foreach ($message['attachments'] as $attachment) {
            $rawContent = file_get_contents($attachment['url']) ?? file_get_contents($attachment['proxy_url']);
            $response = app('ips.api')->postGalleryImage(
                self::DLC_DUNGEONS_ALBUM_ID,
                $userIpsOauth->remote_id,
                $message['content_for_forums'] = trim($message['content_for_forums']),
                $attachment['filename'],
                base64_encode($rawContent),
                $message['timestamp']
            );
            $response !== null && $gallery_image_ids[] = $response['id'];
        }
        $attendance->gallery_image_ids = implode(',', $gallery_image_ids);
        $attendance->save();

        return $message;
    }

    private function getUserForGivenDiscordRemoteId(string $discordRemoteId): ?User
    {
        /** @var UserOAuth $userDiscordOauth */
        $userDiscordOauth = UserOAuth::query()
            ->where('remote_id', '=', $discordRemoteId)
            ->where('remote_provider', '=', 'discord')
            ->with(['owner'])
            ->get()
            ->first();

        return $userDiscordOauth !== null ? $userDiscordOauth->owner : null;
    }

    /**
     * @throws \JsonException
     */
    private function notifyUsersWithoutIpsOauthAccount(): void
    {
        if ($count = count($this->ipsOauthNotFoundList)) {
            $discordApi = app('discord.api');
            foreach ($this->ipsOauthNotFoundList as $discordRemoteId) {
                if (!app()->environment('production')) {
                    $channel = env('DISCORD_TEST_CHANNEL_ID');
                } else {
                    $dmChannel = $discordApi->createDmChannel($discordRemoteId);
                    $channel = $dmChannel['id'];
                }
                $mentionName = sprintf('<@!%s>', $discordRemoteId);
                $discordApi->createMessageInChannel($channel, [
                    RequestOptions::FORM_PARAMS => [
                        'payload_json' => json_encode([
                            'content' => sprintf(
                                '**Regarding Forum Account Not Being Linked**' . PHP_EOL
                                . 'Hello, %s! During Attendance tracking, we noticed you haven\'t linked your Forum account in our Guild Planner. '
                                . 'Please login to Guild Planner at your earliest convenience and link Forum account to your Planner account.',
                                $mentionName
                            ),
                            'tts' => true,
                            'embed' => [
                                'color' => 0xaa0000,
                                'thumbnail' => [
                                    'url' => cloudinary_url('special/logo.png', [
                                        'secure' => true,
                                        'width' => 300,
                                        'height' => 300,
                                    ])
                                ],
                                'fields' => [
                                    [
                                        'name' => 'Guild Planner',
                                        'value' => 'https://planner.lodgeofsorceresses.com',
                                    ]
                                ],
                                'footer' => [
                                    'text' => 'Sent via Lodge of Sorceresses Guild Planner at: https://planner.lodgeofsorceresses.com'
                                ]
                            ],
                        ], JSON_THROW_ON_ERROR),
                    ]
                ]);
            }
            $this->warn(sprintf('%d users were notified about missing IPS accounts.', $count));
            $this->ipsOauthNotFoundList = [];
        }
    }
}

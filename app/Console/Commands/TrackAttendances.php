<?php /** @noinspection NullPointerExceptionInspection */

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use App\Models\UserOAuth;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TrackAttendances extends Command
{
    private const DLC_DUNGEONS_ALBUM_ID = 1;

    private const TEMPLATE_FOR_MENTION_LINKS = '<a contenteditable="false" data-ipshover="" data-ipshover-target="%s" data-mentionid="%s" href="%s">%s</a>';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guild:attendance:track';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tracks posts in #achievements Discord channel for Attendance markers.';

    /**
     * @var string
     */
    private $botId;

    /**
     * @var string
     */
    private $overallLastMessageIdProcessed;

    /**
     * @var string
     */
    private $currentLastMessageIdProcessed;

    /**
     * @var array
     */
    private $ipsOauthNotFoundList = [];

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
                $attendance = $this->persistAttendance($message);
                if ($attendance !== null) {
                    $this->uploadAttachmentToGallery($message, $userIpsOauth, $attendance);
                    $this->info(sprintf('Parsed and Saved: "%s"...', $message['content']));
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

    private function cleanMessage(array $message): array
    {
        # Fetch mentioned IPS OAuth accounts
        $botMentionIndex = null;
        foreach ($message['mentions'] as $key => &$mention) {
            if ($mention['id'] === $this->botId) {
                $botMentionIndex = $key;
                continue;
            }
            $mentionUser = $this->getUserForGivenDiscordRemoteId($mention['id']);
            /** @var UserOAuth $userIpsOauth */
            $mentionUserOauth = $mentionUser->linkedAccounts()->where('remote_provider', 'ips')->first();
            if ($mentionUserOauth !== null) {
                $slug = $mentionUserOauth->remote_id . '-member';
                $formattedMention = sprintf(
                    self::TEMPLATE_FOR_MENTION_LINKS,
                    '<___base_url___>/profile/' . $slug . '/?do=hovercard',
                    $mentionUserOauth->remote_id,
                    '<___base_url___>/profile/' . $slug . '/',
                    $mentionUserOauth->nickname
                );
                $message['content'] = preg_replace('/<@!' . $mention['id'] . '>/', $formattedMention, $message['content']);
                $mention = $mentionUser;
            } else {
                if (!in_array($mention['id'], $this->ipsOauthNotFoundList, true)) {
                    $message['content'] = preg_replace('/<@!' . $mention['id'] . '>/', '', $message['content']);
                    $this->ipsOauthNotFoundList[] = $mention['id'];
                }
                $this->warn('No IPS account for user: ' . $mention['username']);
            }
        }
        unset($mention);
        if ($botMentionIndex !== null) {
            unset($message['mentions'][$botMentionIndex]);

            # Replace bot mention from the message content
            $message['content'] = preg_replace('/<@!' . $this->botId . '>/', '', $message['content']);
        }

        # Remove mentions not allowed by Discord
        $message['content'] = preg_replace('/<?@[\S]+/', ' ', $message['content']);

        return $message;
    }

    /**
     * @param array $message
     *
     * @return \App\Models\Attendance|null
     * @throws \Exception
     */
    public function persistAttendance(array $message): ?Attendance
    {
        $attendance = Attendance::query()->where('discord_message_id', '=', $message['id'])->get()->first();
        if ($attendance !== null) {
            return null;
        }

        $attendance = new Attendance();
        $attendance->text = $message['content'];
        $attendance->discord_message_id = $message['id'];
        $attendance->created_by = $message['author']->id;
        $attendance->created_at = new CarbonImmutable($message['timestamp']);
        $attendance->isDirty() && $attendance->save();

        $collectionOfMentions = collect($message['mentions']);
        $collectionOfMentions->add($message['author']);
        $attendance->attendees()->sync($collectionOfMentions->pluck('id'));
        $attendance->save();

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
                $message['content'] = trim($message['content']),
                $attachment['filename'],
                base64_encode($rawContent)
            );
            $gallery_image_ids[] = $response['id'];
        }
        $attendance->gallery_image_ids = implode(',', $gallery_image_ids);
        $attendance->save();

        return $message;
    }

    private function getUserForGivenDiscordRemoteId(string $discordRemoteId): User
    {
        /** @var UserOAuth $userDiscordOauth */
        $userDiscordOauth = UserOAuth::query()
            ->where('remote_id', '=', $discordRemoteId)
            ->where('remote_provider', '=', 'discord')
            ->with(['owner'])
            ->get()
            ->first();

        return $userDiscordOauth->owner;
    }
}

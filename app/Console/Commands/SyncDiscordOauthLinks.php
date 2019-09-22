<?php

namespace App\Console\Commands;

use App\Models\UserOAuth;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Http\Response;

class SyncDiscordOauthLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes Discord OAuth user roles.';

    /**
     * @var \App\Services\DiscordApi
     */
    private $discordApi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->discordApi = app('discord.api');
    }

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): void
    {
        /** @var UserOAuth|\Illuminate\Database\Eloquent\Collection $oauthAccount */
        $oauthAccounts = UserOAuth::whereRemoteProvider('discord')->get();
        $oauthAccount = $oauthAccounts->shift();
        while ($oauthAccount) {
            $headers = $this->discordApi->getLastResponseHeaders();
            $memberNameParsed = '[' . $oauthAccount->name . ']';
            if (isset($headers['X-RateLimit-Remaining']) && $headers['X-RateLimit-Remaining'] === '0') {
                $sleepDuration = (int)$headers['X-RateLimit-Reset'] - time();
                sleep($sleepDuration);
            }

            try {
                $remoteUserDataFetchedThroughApi = $this->discordApi->getGuildMember($oauthAccount->remote_id);
                $oauthAccount->remote_secondary_groups = !empty($remoteUserDataFetchedThroughApi['roles'])
                    ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
                    : null;
                $oauthAccount->touch();
                $oauthAccount->save();
                $this->info($memberNameParsed . ' Successfully synced.');
            } catch (ClientException $e) {
                if ($e->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                    preg_match('/"retry_after": (\d+)/', $e->getMessage(), $retryAfterMatch);
                    $microSecondsToWait = (int)$retryAfterMatch[1] * 1000;
                    usleep($microSecondsToWait);
                    $this->warn($memberNameParsed . ' Being rated... Waiting for ' . $microSecondsToWait . ' microsecs.');
                    continue;
                }
                if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                    $this->error($memberNameParsed . ' Member not found, deleting account...');
                    $user = $oauthAccount->owner;
                    if ($user) {
                        try {
                            $user->forceDelete();
                            $this->warn($memberNameParsed . ' Deleted.');
                        } catch (Exception $e) {
                            $this->error($memberNameParsed . ' Failed to delete: ' . $e->getMessage());
                        }
                    }
                }
            }
            $oauthAccount = $oauthAccounts->shift();
        }
        $this->info('Discord OAuth accounts successfully synced!');
    }
}

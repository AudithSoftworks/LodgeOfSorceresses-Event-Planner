<?php

namespace App\Console\Commands;

use App\Models\UserOAuth;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

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
    public function handle(): bool
    {
        /** @var UserOAuth|\Illuminate\Database\Eloquent\Collection $oauthAccount */
        $oauthAccounts = UserOAuth::whereRemoteProvider('discord')->get();
        $oauthAccount = $oauthAccounts->shift();
        while ($oauthAccount) {
            $headers = $this->discordApi->getLastResponseHeaders();
            if (isset($headers['X-RateLimit-Remaining']) && $headers['X-RateLimit-Remaining'] === '0') {
                $sleepDuration = (int)$headers['X-RateLimit-Reset'] - time();
                sleep($sleepDuration);
            }

            try {
                $remoteUserDataFetchedThroughApi = $this->discordApi->getGuildMember($oauthAccount->remote_id);
                $oauthAccount->remote_secondary_groups = count($remoteUserDataFetchedThroughApi['roles'])
                    ? implode(',', $remoteUserDataFetchedThroughApi['roles'])
                    : null;
                $oauthAccount->touch();
                $oauthAccount->save();
                $this->info('Successfully synced ' . $oauthAccount->name);
            } catch (RequestException $e) {
                $this->warn('RequestException occured for ' . $oauthAccount->name);
                continue;
            }
            $oauthAccount = $oauthAccounts->shift();
        }
        $this->info('Discord OAuth accounts successfully synced!');

        return true;
    }
}

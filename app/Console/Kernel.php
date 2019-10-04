<?php

namespace App\Console;

use App\Console\Commands\FetchEventsUsingIpsApi;
use App\Console\Commands\PruneOrphanedFiles;
use App\Console\Commands\SyncOauthLinks;
use App\Console\Commands\SyncYoutubeRssFeeds;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private const LOG_FILE = '/var/log/lodgeofsorceresses.log';
    
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(FetchEventsUsingIpsApi::class)->hourly()->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(SyncOauthLinks::class)->everyFiveMinutes()->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(PruneOrphanedFiles::class)->weekly()->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(SyncYoutubeRssFeeds::class)->dailyAt('05:00')->sendOutputTo(self::LOG_FILE, true);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}

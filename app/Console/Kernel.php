<?php

namespace App\Console;

use App\Console\Commands\FetchEventsUsingIpsApi;
use App\Console\Commands\PruneOrphanedFiles;
use App\Console\Commands\SyncDiscordOauthLinks;
use App\Console\Commands\SyncYoutubeRssFeeds;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(FetchEventsUsingIpsApi::class)->hourly();
        $schedule->command(SyncDiscordOauthLinks::class)->everyFiveMinutes()->withoutOverlapping();
        $schedule->command(PruneOrphanedFiles::class)->weekly();
        $schedule->command(SyncYoutubeRssFeeds::class)->dailyAt('05:00');
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

<?php /** @noinspection PhpIncludeInspection */

namespace App\Console;

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
        $timezone = config('app.timezone');

        $schedule->command(Commands\AnnounceDailyMidgameEventsOnDiscord::class)->dailyAt('06:30')->timezone($timezone)->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(Commands\PruneOrphanedFiles::class)->weekly()->timezone($timezone)->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(Commands\RequestDpsParseRenewal::class)->monthlyOn(15, '05:00')->timezone($timezone)->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(Commands\SyncOauthAccounts::class)->dailyAt('03:00')->timezone($timezone)->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(Commands\SyncYoutubeRssFeeds::class)->dailyAt('03:15')->timezone($timezone)->sendOutputTo(self::LOG_FILE, true);
        $schedule->command(Commands\TrackAttendances::class)->cron('0 */6 * * *')->timezone($timezone)->sendOutputTo(self::LOG_FILE, true);
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

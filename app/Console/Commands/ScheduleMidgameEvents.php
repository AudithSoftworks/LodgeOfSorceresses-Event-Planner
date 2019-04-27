<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduleMidgameEvents extends Command
{
    public const DUNGEON_FALKREATH_HOLD = 'falkreath_hold';

    public const DUNGEON_FANG_LAIR = 'fang_lair';

    public const DUNGEON_SCALECALLER_PEAK = 'scalecaller_peak';

    public const DUNGEON_MOONHUNTER_KEEP = 'moonhunter_keep';

    public const DUNGEON_MARCH_OF_SACRIFICES = 'march_of_sacrifices';

    public const DUNGEON_DEPTHS_OF_MALATAR = 'depths_of_malatar';

    public const DUNGEON_FROSTVAULT = 'frostvault';

    public const DUNGEON_IMPERIAL_CITY_PRISON = 'imperial_city_prison';

    public const DUNGEON_RUINS_OF_MAZZATUN = 'ruins_of_mazzatun';

    public const DUNGEON_WHITE_GOLD_TOWER = 'white_gold_tower';

    public const DUNGEON_CRADLE_OF_SHADOWS = 'cradle_of_shadows';

    public const DUNGEON_BLOODROOT_FORGE = 'bloodroot_forge';

    private const EVENTS = [
        self::DUNGEON_FALKREATH_HOLD => ['initialDate' => '2019-04-07', 'youtubeVideos' => ['0ZWrf40iuvo'], 'guideUrl' => 'http://elderscrollsonline.wiki.fextralife.com/Falkreath+Hold'],
        self::DUNGEON_FANG_LAIR => ['initialDate' => '2019-04-08', 'youtubeVideos' => ['wQQ8jf-bY9I'], 'guideUrl' => 'https://alcasthq.com/eso-fang-lair-guide/'],
        self::DUNGEON_SCALECALLER_PEAK => ['initialDate' => '2019-04-09', 'youtubeVideos' => ['QV10nBgOfnU'], 'guideUrl' => 'https://alcasthq.com/eso-scalecaller-peak-guide/'],
        self::DUNGEON_MOONHUNTER_KEEP => ['initialDate' => '2019-04-10', 'youtubeVideos' => ['nMdw5nWH3u4'], 'guideUrl' => null],
        self::DUNGEON_MARCH_OF_SACRIFICES => ['initialDate' => '2019-04-11', 'youtubeVideos' => ['uZKL8RrAUMQ'], 'guideUrl' => 'https://alcasthq.com/eso-march-of-sacrifices-guide/'],
        self::DUNGEON_DEPTHS_OF_MALATAR => ['initialDate' => '2019-04-12', 'youtubeVideos' => ['g5Ly3S2DJMo', 've3bIGbc1io'], 'guideUrl' => 'https://alcasthq.com/eso-depths-of-malatar-guide/'],
        self::DUNGEON_FROSTVAULT => ['initialDate' => '2019-04-13', 'youtubeVideos' => ['nKATIqR6pxM'], 'guideUrl' => 'https://alcasthq.com/eso-frostvault-guide/'],
        self::DUNGEON_IMPERIAL_CITY_PRISON => ['initialDate' => '2019-04-14', 'youtubeVideos' => ['yZhctdKYD-8'], 'guideUrl' => 'https://elderscrollsonline.info/guides/imperial-city-prison'],
        self::DUNGEON_RUINS_OF_MAZZATUN => ['initialDate' => '2019-04-15', 'youtubeVideos' => ['dnC3-9i1sP4'], 'guideUrl' => null],
        self::DUNGEON_WHITE_GOLD_TOWER => ['initialDate' => '2019-04-16', 'youtubeVideos' => [], 'guideUrl' => 'http://elderscrollsonline.wiki.fextralife.com/White-Gold+Tower'],
        self::DUNGEON_CRADLE_OF_SHADOWS => ['initialDate' => '2019-04-17', 'youtubeVideos' => ['FmVDEQBVmj8'], 'guideUrl' => 'http://elderscrollsonline.wiki.fextralife.com/Cradle+of+Shadows'],
        self::DUNGEON_BLOODROOT_FORGE => ['initialDate' => '2019-04-18', 'youtubeVideos' => ['AVMRP3l18eM'], 'guideUrl' => 'https://alcasthq.com/eso-bloodroot-forge-guide/'],
    ];

    private const DUNGEON_ROTATION_PERIOD_IN_DAYS = 12;

    /**
     * @inheritdoc
     */
    protected $signature = 'ips:schedule:midgame';

    /**
     * @inheritdoc
     */
    protected $description = 'Schedules Midgame content on Forum Calendar.';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $dungeonsToSchedule = [];
        $tomorrow = new Carbon('tomorrow');
        foreach (self::EVENTS as $dungeonName => $dungeonDetails) {
            $initialDay = new Carbon($dungeonDetails['initialDate']);
            $diffInDays = $initialDay->diffInDays($tomorrow, false);
            $diffInDaysPeriodicDistance = $diffInDays % self::DUNGEON_ROTATION_PERIOD_IN_DAYS;
            if ($diffInDaysPeriodicDistance !== 0 && $diffInDaysPeriodicDistance !== self::DUNGEON_ROTATION_PERIOD_IN_DAYS - 1) {
                continue;
            }
            $dungeonsToSchedule[$dungeonName] = $diffInDaysPeriodicDistance === 0
                ? $tomorrow->clone()->setHour(18)->setMinute(30)
                : $tomorrow->clone()->addDay()->setHour(18)->setMinute(30);
        }

        $this->scheduleViaIpsApi($dungeonsToSchedule);
    }

    private function scheduleViaIpsApi(array $dungeonsToSchedule)
    {
        $ipsApi = app('ips.api');
        /**
         * @var string $dungeonName
         * @var Carbon $date
         */
        foreach ($dungeonsToSchedule as $dungeonName => $date) {
            $title = ucwords(str_replace('_', ' ', 'veteran_' . $dungeonName));
            $description = $this->buildEventDescription($dungeonName);
            $eventCreated = $ipsApi->createCalendarEvent($title, $description, $date);
            $this->info('Calendar event #' . $eventCreated['id'] . ' was created.');
        }
    }

    private function buildEventDescription(string $dungeonName)
    {
        $descriptionFragments = [];

        $title = ucwords(str_replace('_', ' ', 'veteran_' . $dungeonName));
        $guideLink = self::EVENTS[$dungeonName]['guideUrl'] ? '<a href="' . self::EVENTS[$dungeonName]['guideUrl'] . '" rel="external nofollow"><b>' . $title . '</b></a>' : '<b>' . $title . '</b>';
        $descriptionFragments[] = '<p>We will be doing today\'s DLC pledge dungeon - ' . $guideLink . '.</p>';
        $descriptionFragments[] = '<p>Please signup so that we could organize groups every evening. Official Midgame event time is 8:30pm every day. But of course, as always, please feel free doing the content some other time during the day as well. By default, the Midgame event for current day is the DLC Pledge of the day.</p>';
        $descriptionFragments[] = '<p>To be credited for your attendances, please don\'t forget to upload screenshots in <a href="https://lodgeofsorceresses.com/gallery/album/1-dlc-dungeons/" rel="">DLC Dungeons</a> or <a href="https://lodgeofsorceresses.com/gallery/album/3-veteran-arenas/" rel="">Arenas</a> albums. In those screenshots, please make sure client UI is hidden, and nameplates are enabled (with ESO IDs visible, instead of character names).</p>';
        $descriptionFragments[] = '<p>Signups to Midgame events are enabled for <em>Neophyte or above ranks.</em></p>';

        foreach (self::EVENTS[$dungeonName]['youtubeVideos'] as $videoId) {
            $descriptionFragments[] = '<div class="ipsEmbeddedVideo" contenteditable="false"><div>
                <iframe allow="autoplay; encrypted-media" allowfullscreen="true" frameborder="0" height="270" id="ips_uid_5355_6" width="480" src="https://www.youtube.com/embed/' . $videoId . '?feature=oembed"></iframe>
            </div></div>';
        }

        return implode("\n", $descriptionFragments);
    }
}

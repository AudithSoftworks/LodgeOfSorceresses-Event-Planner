<?php

namespace App\Console\Commands;

use App\Models\DpsParse;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class RequestDpsParseRenewal extends Command
{
    private const SOFT_TIMEOUT = 30; // days

    private const HARD_TIMEOUT = 40; // days

    protected $signature = 'clearance:request-dps-renewal';

    protected $description = 'Checks all DPS Parses and notifies the user if their Parse is outdated.';

    private $parsesWithSoftTimeout = [];

    private $parsesWithHardTimeout = [];

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        $this->info('Checking all characters of Tier-1 and above for their parses\' age...');

        $parsesOfTierOneOrAboveCharacters = DpsParse::query()
            ->whereNotNull('processed_by')
            ->with(['character'])
            ->whereHas('character', static function (Builder $query) {
                $query->where('approved_for_tier', '>', 0);
            })
            ->orderByDesc('created_at')
            ->get();
        foreach ($parsesOfTierOneOrAboveCharacters as $dpsParse) {
            if (
                ($this->parsesWithHardTimeout[$dpsParse->owner->id][$dpsParse->character->id] ?? null) !== null
                || ($this->parsesWithSoftTimeout[$dpsParse->owner->id][$dpsParse->character->id] ?? null) !== null
            ) {
                $this->warn(sprintf('Skipped duplicate character (id: %d)', $dpsParse->character->id));
                continue;
            }
            if ($dpsParse->created_at->addDays(self::HARD_TIMEOUT)->isBefore(new Carbon())) {
                if (($this->parsesWithHardTimeout[$dpsParse->owner->id][$dpsParse->character->id] ?? null) === null) {
                    $this->parsesWithHardTimeout[$dpsParse->owner->id][$dpsParse->character->id] = $dpsParse;
                }

                /** @var DpsParse $newerParse */
                $newerParse = $this->parsesWithHardTimeout[$dpsParse->owner->id][$dpsParse->character->id];
                $this->warn(sprintf(
                    'An older parse (created at: %s) skipped in favor of the newer one (created at: %s).',
                    $dpsParse->created_at->format('Y-m-d'),
                    $newerParse->created_at->format('Y-m-d')
                ));
                continue;
            }
            if ($dpsParse->created_at->addDays(self::SOFT_TIMEOUT)->isBefore(new Carbon())) {
                if (($this->parsesWithSoftTimeout[$dpsParse->owner->id][$dpsParse->character->id] ?? null) === null) {
                    $this->parsesWithSoftTimeout[$dpsParse->owner->id][$dpsParse->character->id] = $dpsParse;
                }

                /** @var DpsParse $newerParse */
                $newerParse = $this->parsesWithSoftTimeout[$dpsParse->owner->id][$dpsParse->character->id];
                $this->warn(sprintf(
                    'An older parse (created at: %s) skipped in favor of the newer one (created at: %s).',
                    $dpsParse->created_at->format('Y-m-d'),
                    $newerParse->created_at->format('Y-m-d')
                ));
            }
        }

        $this->info('Notifying related users...');

        $this->notify($this->parsesWithHardTimeout, true);
        $this->notify($this->parsesWithSoftTimeout);

        $this->info('DONE.');

        return true;
    }

    private function notify(array $parsesGroupedByUserIdAndCharacterId, bool $isHardTimeout = false): void
    {
        $discordApi = app('discord.api');
        /** @var int $userId */
        foreach ($parsesGroupedByUserIdAndCharacterId as $userId => $parsesGroupedByCharacterId) {
            Cache::has('user-' . $userId); // Recache trigger.
            /** @var \App\Models\User $user */
            $user = Cache::get('user-' . $userId);
            if ($user->trashed()) {
                continue;
            }
            /** @var \App\Models\UserOAuth $parseOwnersDiscordAccount */
            $parseOwnersDiscordAccount = $user->linkedAccountsParsed->get('discord');
            $dmChannel = $parseOwnersDiscordAccount ? $discordApi->createDmChannel($parseOwnersDiscordAccount->remote_id) : null;
            if ($dmChannel) {
                $fields = [];
                /**
                 * @var int $characterId
                 * @var DpsParse $dpsParse
                 */
                foreach ($parsesGroupedByCharacterId as $characterId => $dpsParse) {
                    Cache::has('character-' . $characterId); // Recache trigger.
                    /** @var \App\Models\Character $character */
                    $character = Cache::get('character-' . $characterId);
                    $fields[] = [
                        'name' => $character->name,
                        'value' => sprintf('Last Parse with %d DPS.' . PHP_EOL . 'Submitted on %s', $dpsParse->dps_amount, $dpsParse->created_at->format('Y-m-d'))
                    ];
                }
                $discordApi->createMessageInChannel($dmChannel['id'], [
                    RequestOptions::FORM_PARAMS => [
                        'payload_json' => json_encode([
                            'content' => 'Hello! Your DPS Parses submitted for the following DDs are more than ' . (!$isHardTimeout ? self::SOFT_TIMEOUT : self::HARD_TIMEOUT) . ' days old. '
                                . '**Characters with DPS Parses older than 40 days may lose their content clearance**. '
                                . 'Please renew your parses ASAP!' . PHP_EOL . 'P.S.: This check is regularly done once every month.',
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
                                'fields' => $fields,
                                'footer' => [
                                    'text' => 'Sent via Lodge of Sorceresses Planner at: https://planner.lodgeofsorceresses.com'
                                ]
                            ],
                        ]),
                    ]
                ]);
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Events\Character\CharacterReset;
use App\Models\Character;
use App\Models\DpsParse;
use App\Services\GuildRanksAndClearance;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class ResetRanksByInvalidatingDpsParses extends Command
{
    /**
     * @var string
     */
    protected $signature = 'parses:reset {date : YYYYMMDD-format date up to which the existing DPS Parses will be invalidated}';

    /**
     * @var string
     */
    protected $description = 'Invalidates existing DPS Parses and re-ranks the Roster (mostly after an Update release).';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $date = $this->argument('date');
        try {
            $dateFormatted = new CarbonImmutable($date);
        } catch (Exception $e) {
            $this->error('Date argument is not a valid date in YYYYMMDD format!');

            return;
        }

        $queryForDpsParsesToSoftDelete = DpsParse::query()->where('created_at', '<=', $dateFormatted);
        $idsOfCharactersAffected = $queryForDpsParsesToSoftDelete->get('character_id')->pluck('character_id')->unique();
        $queryForDpsParsesToSoftDelete->delete();

        $this->processExistingValidDpsParsesAgainForGivenCharacterIds($idsOfCharactersAffected);
    }

    private function processExistingValidDpsParsesAgainForGivenCharacterIds(Collection $idsOfCharactersAffected): void
    {
        $guildRankService = app('guild.ranks.clearance');
        $nrOfCharactersAffected = $idsOfCharactersAffected->count();
        $nrOfDpsParsesProcessed = 0;
        /** @var Character $character */
        $characters = Character::query()
            ->whereIn('id', $idsOfCharactersAffected)
            ->with([
                'dpsParses' => static function (HasMany $query) {
                    $query->whereNotNull('processed_by')->orderBy('created_at');
                }
            ])
            ->get();
        foreach ($characters as $character) {
            $this->info('Character (id: ' . $character->id . ' with ' . $character->dpsParses->count() . ' processable parses) content clearance was reset.');
            $character->approved_for_tier = GuildRanksAndClearance::CLEARANCE_TIER_0;
            $character->save();

            $dpsParses = $character->dpsParses;
            foreach ($dpsParses as $dpsParse) {
                $guildRankService->processDpsParse($dpsParse);
                $this->info('DpsParse (id: ' . $dpsParse->id . ') for Character (id: ' . $character->id . ') was processed.');
                ++$nrOfDpsParsesProcessed;
            }

            Event::dispatch(new CharacterReset($character));
        }
        $this->info($nrOfCharactersAffected . ' Characters with their existing ' . $nrOfDpsParsesProcessed . ' DpsParses processed.');
    }
}

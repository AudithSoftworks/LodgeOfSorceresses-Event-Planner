<?php namespace App\Services;

use App\Models\DpsParse as DpsParseModel;
use UnexpectedValueException;

class DpsParse
{
    /**
     * @var array
     */
    private $dpsRequirementsMap;

    public function __construct()
    {
        $this->dpsRequirementsMap = config('dps_clearance');
    }

    /**
     * @param \App\Models\DpsParse $dpsParse
     *
     * @return bool
     */
    public function process(DpsParseModel $dpsParse): bool
    {
        $dpsParse->load('character');
        /** @var \App\Models\Character $character */
        $character = $dpsParse->character()->first();
        $class = $character->class;
        $role = $character->role;

        $dpsRequirement = $this->dpsRequirementsMap[$class][$role] ?? null;
        if (!$dpsRequirement) {
            throw new UnexpectedValueException('Invalid class or role value encountered!');
        }

        foreach (GuildRankAndClearance::CLEARANCE_LEVELS as $clearanceLevel => $clearanceLevelDetails) {
            if ($dpsParse->dps_amount < $dpsRequirement[$clearanceLevel]) {
                if ($character->{'approved_for_' . $clearanceLevel}) {
                    $character->{'approved_for_' . $clearanceLevel} = false;
                }
                continue;
            }
            if (!$character->{'approved_for_' . $clearanceLevel}) {
                $character->{'approved_for_' . $clearanceLevel} = true;
            }
        }
        $character->last_submitted_dps_amount = $dpsParse->dps_amount;
        $character->save();

        return true;
    }
}

<?php namespace App\Services;

use App\Models\Character;
use App\Models\DpsParse;
use App\Models\User;
use UnexpectedValueException;

class GuildRanksAndClearance
{
    public const RANK_SOULSHRIVEN = [
        'title' => 'Soulshriven',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_SOULSHRIVEN,
        'discordRole' => DiscordApi::ROLE_SOULSHRIVEN,
        'discordShrivenRole' => DiscordApi::ROLE_SOULSHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_INITIATE = [
        'title' => 'Initiate',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_INITIATE,
        'discordRole' => DiscordApi::ROLE_INITIATE,
        'discordShrivenRole' => DiscordApi::ROLE_INITIATE_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_NEOPHYTE = [
        'title' => 'Neophyte',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_NEOPHYTE,
        'discordRole' => DiscordApi::ROLE_NEOPHYTE,
        'discordShrivenRole' => DiscordApi::ROLE_NEOPHYTE_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_PRACTICUS = [
        'title' => 'Practicus',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_PRACTICUS,
        'discordRole' => DiscordApi::ROLE_PRACTICUS,
        'discordShrivenRole' => DiscordApi::ROLE_PRACTICUS_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_MINOR = [
        'title' => 'Adeptus Minor',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_ADEPTUS_MINOR,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_MINOR,
        'discordShrivenRole' => DiscordApi::ROLE_ADEPTUS_MINOR_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_MAJOR = [
        'title' => 'Adeptus Major',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_ADEPTUS_MAJOR,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_MAJOR,
        'discordShrivenRole' => DiscordApi::ROLE_ADEPTUS_MAJOR_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_DOMINUS_LIMINIS = [
        'title' => 'Dominus Liminis',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_DOMINUS_LIMINIS,
        'discordRole' => DiscordApi::ROLE_DOMINUS_LIMINIS,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_EXEMPTUS = [
        'title' => 'Adeptus Exemptus',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_ADEPTUS_EXEMPTUS,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_EXEMPTUS,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_MAGISTER_TEMPLI = [
        'title' => 'Magister Templi',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI,
        'discordRole' => DiscordApi::ROLE_MAGISTER_TEMPLI,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => true,
    ];

    public const RANK_IPSISSIMUS = [
        'title' => 'Magister Templi',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_IPSISSIMUS,
        'discordRole' => DiscordApi::ROLE_MAGISTER_TEMPLI,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => true,
    ];

    public const CLEARANCE_TIER_0 = 0;

    public const CLEARANCE_TIER_1 = 1;

    public const CLEARANCE_TIER_2 = 2;

    public const CLEARANCE_TIER_3 = 3;

    public const CLEARANCE_TIER_4 = 4;

    public const CLEARANCE_LEVELS = [
        self::CLEARANCE_TIER_1 => [
            'title' => 'Tier-1 Content',
            'rank' => self::RANK_NEOPHYTE,
        ],
        self::CLEARANCE_TIER_2 => [
            'title' => 'Tier-2 Content',
            'rank' => self::RANK_PRACTICUS,
        ],
        self::CLEARANCE_TIER_3 => [
            'title' => 'Tier-3 Content',
            'rank' => self::RANK_ADEPTUS_MINOR,
        ],
        self::CLEARANCE_TIER_4 => [
            'title' => 'Tier-4 Content',
            'rank' => self::RANK_ADEPTUS_MAJOR,
        ],
    ];

    public function calculateClearanceLevelOfUser(User $user): int
    {
        $tableDbConnection = app('db.connection')->table('characters');
        $result = $tableDbConnection->selectRaw('MAX(approved_for_tier) AS max')->where('user_id', $user->id)->get('max')->first();

        return $result && $result->max !== null ? $result->max : 0;
    }

    public function determineIfUserHasOtherRankedCharactersWithGivenRole(User $user, int $role): bool
    {
        $resultSet = $user->characters()->where('role', $role)->where('approved_for_tier', '>', self::CLEARANCE_TIER_0)->first();

        return !empty($resultSet);
    }

    public function processDpsParse(DpsParse $dpsParse): bool
    {
        $dpsParse->loadMissing('character');
        $character = $dpsParse->character;
        $class = $character->class;
        $role = $character->role;

        $dpsRequirementsMap = config('dps_clearance');
        $dpsRequirement = $dpsRequirementsMap[$class][$role] ?? null;
        if (!$dpsRequirement) {
            throw new UnexpectedValueException('Invalid class or role value encountered!');
        }

        $character->approved_for_tier = self::CLEARANCE_TIER_0;
        foreach (self::CLEARANCE_LEVELS as $clearanceLevel => $clearanceLevelDetails) {
            if ($dpsParse->dps_amount < $dpsRequirement[$clearanceLevel]) {
                break;
            }
            $character->approved_for_tier = $clearanceLevel;
        }
        $character->last_submitted_dps_amount = $dpsParse->dps_amount;

        $setsWornDuringParse = collect(explode(',', $dpsParse->sets));
        $characterSets = collect(explode(',', $character->sets));
        $setsToBeAddedToCharacter = $setsWornDuringParse->diff($characterSets);
        if ($setsToBeAddedToCharacter->count()) {
            $character->sets = $setsToBeAddedToCharacter->merge($characterSets)->unique()->implode(',');
        }

        $character->save();

        return true;
    }

    public function promoteCharacter(Character $character): bool
    {
        if ($character->approved_for_tier === self::CLEARANCE_TIER_4) {
            throw new UnexpectedValueException('Failed! Character already has the highest clearance level.');
        }
        ++$character->approved_for_tier;

        return $character->isDirty() && $character->save();
    }

    public function demoteCharacter(Character $character): bool
    {
        if ($character->approved_for_tier === self::CLEARANCE_TIER_0) {
            throw new UnexpectedValueException('Failed! Character already has the lowest clearance level.');
        }
        --$character->approved_for_tier;

        return $character->isDirty() && $character->save();
    }
}

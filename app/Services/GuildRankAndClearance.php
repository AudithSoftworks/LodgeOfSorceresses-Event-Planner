<?php namespace App\Services;

use App\Models\Character;
use App\Models\DpsParse;
use App\Models\User;
use UnexpectedValueException;

class GuildRankAndClearance
{
    public const RANK_SOULSHRIVEN = [
        'title' => 'Soulshriven',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_SOULSHRIVEN,
        'discordRole' => DiscordApi::ROLE_SOULSHRIVEN,
        'isMember' => false,
        'isAdmin' => false,
    ];

    public const RANK_INITIATE = [
        'title' => 'Initiate',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_INITIATE,
        'discordRole' => DiscordApi::ROLE_INITIATE,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_NEOPHYTE = [
        'title' => 'Neophyte',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_NEOPHYTE,
        'discordRole' => DiscordApi::ROLE_NEOPHYTE,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_PRACTICUS = [
        'title' => 'Practicus',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_PRACTICUS,
        'discordRole' => DiscordApi::ROLE_PRACTICUS,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_MINOR = [
        'title' => 'Adeptus Minor',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_ADEPTUS_MINOR,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_MINOR,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_MAJOR = [
        'title' => 'Adeptus Major',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_ADEPTUS_MAJOR,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_MAJOR,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_DOMINUS_LIMINIS = [
        'title' => 'Dominus Liminis',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_DOMINUS_LIMINIS,
        'discordRole' => DiscordApi::ROLE_DOMINUS_LIMINIS,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_RECTOR = [
        'title' => 'Rector',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_RECTOR,
        'discordRole' => DiscordApi::ROLE_RECTOR,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_MAGISTER_TEMPLI = [
        'title' => 'Magister Templi',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI,
        'discordRole' => DiscordApi::ROLE_MAGISTER_TEMPLI,
        'isMember' => true,
        'isAdmin' => true,
    ];

    public const RANK_IPSISSIMUS = [
        'title' => 'Magister Templi',
        'ipsGroupId' => IpsApi::MEMBER_GROUPS_IPSISSIMUS,
        'discordRole' => DiscordApi::ROLE_MAGISTER_TEMPLI,
        'isMember' => true,
        'isAdmin' => true,
    ];

    public const CLEARANCE_TIER_1 = 't1';

    public const CLEARANCE_TIER_2 = 't2';

    public const CLEARANCE_TIER_3 = 't3';

    public const CLEARANCE_TIER_4 = 't4';

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

    public function calculateTopClearanceForUser(User $user): ?string
    {
        $user->loadMissing(['characters']);
        /** @var \App\Models\Character[] $characters */
        $parseAuthorsCharacters = $user->characters()->get();

        $topClearanceExisting = null;
        foreach (self::CLEARANCE_LEVELS as $clearanceLevel => $clearanceLevelDetails) {
            $parseAuthorHasThisClearanceInACharacter = false;
            foreach ($parseAuthorsCharacters as $character) {
                if ($character->{'approved_for_' . $clearanceLevel}) {
                    $topClearanceExisting = $clearanceLevel;
                    $parseAuthorHasThisClearanceInACharacter = true;
                    break;
                }
            }
            if (!$parseAuthorHasThisClearanceInACharacter) {
                break;
            }
        }

        return $topClearanceExisting;
    }

    public function calculateTopClearanceForCharacter(Character $character): ?string
    {
        $topClearanceExisting = null;
        foreach (self::CLEARANCE_LEVELS as $clearanceLevel => $clearanceLevelDetails) {
            if ($character->{'approved_for_' . $clearanceLevel}) {
                $topClearanceExisting = $clearanceLevel;
            }
        }

        return $topClearanceExisting;
    }

    public function processDpsParse(DpsParse $dpsParse): bool
    {
        $dpsParse->loadMissing('character');
        /** @var \App\Models\Character $character */
        $character = $dpsParse->character()->first();
        $class = $character->class;
        $role = $character->role;

        $dpsRequirementsMap = config('dps_clearance');
        $dpsRequirement = $dpsRequirementsMap[$class][$role] ?? null;
        if (!$dpsRequirement) {
            throw new UnexpectedValueException('Invalid class or role value encountered!');
        }

        foreach (self::CLEARANCE_LEVELS as $clearanceLevel => $clearanceLevelDetails) {
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

    public function promoteCharacter(Character $character): bool
    {
        $promoted = false;
        foreach (self::CLEARANCE_LEVELS as $clearanceLevel => $clearanceLevelDetails) {
            if (!$character->{'approved_for_' . $clearanceLevel}) {
                $character->{'approved_for_' . $clearanceLevel} = true;
                $promoted = true;
                break;
            }
        }

        if (!$promoted) {
            throw new UnexpectedValueException('Character Not Promoted! Maybe they already have the top clearance level?');
        }

        return $character->save();
    }

    public function demoteCharacter(Character $character): bool
    {
        $demoted = false;
        $clearanceLevelsInReverse = array_reverse(self::CLEARANCE_LEVELS);
        foreach ($clearanceLevelsInReverse as $clearanceLevel => $clearanceLevelDetails) {
            if ($character->{'approved_for_' . $clearanceLevel}) {
                $character->{'approved_for_' . $clearanceLevel} = false;
                $demoted = true;
                break;
            }
        }

        if (!$demoted) {
            throw new UnexpectedValueException('Character Not Demoted! Maybe they already have the lowest clearance level?');
        }

        return $character->save();
    }
}

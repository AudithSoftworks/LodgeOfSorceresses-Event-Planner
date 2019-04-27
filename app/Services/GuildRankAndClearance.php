<?php namespace App\Services;

use App\Models\Character;
use App\Models\User;

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

    public const CLEARANCE_MIDGAME = 'midgame';

    public const CLEARANCE_ENDGAME_TIER_0 = 'endgame_t0';

    public const CLEARANCE_ENDGAME_TIER_1 = 'endgame_t1';

    public const CLEARANCE_ENDGAME_TIER_2 = 'endgame_t2';

    public const CLEARANCE_LEVELS = [
        self::CLEARANCE_MIDGAME => [
            'title' => 'Midgame',
            'rank' => self::RANK_NEOPHYTE,
        ],
        self::CLEARANCE_ENDGAME_TIER_0 => [
            'title' => 'Craglorn vTrials',
            'rank' => self::RANK_PRACTICUS,
        ],
        self::CLEARANCE_ENDGAME_TIER_1 => [
            'title' => 'Tier-1 Endgame',
            'rank' => self::RANK_ADEPTUS_MINOR,
        ],
        self::CLEARANCE_ENDGAME_TIER_2 => [
            'title' => 'Tier-2 Endgame',
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
}

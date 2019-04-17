<?php namespace App\Services;

use App\Models\Character;
use App\Models\User;

class GuildRankAndClearance
{
    public const RANK_SOULSHRIVEN = [
        'title' => 'Soulshriven',
        'member_group_id' => IpsApi::MEMBER_GROUPS_SOULSHRIVEN,
        'discord_role' => DiscordApi::ROLE_SOULSHRIVEN,
    ];

    public const RANK_INITIATE = [
        'title' => 'Initiate',
        'member_group_id' => IpsApi::MEMBER_GROUPS_INITIATE,
        'discord_role' => DiscordApi::ROLE_INITIATE,
    ];

    public const RANK_NEOPHYTE = [
        'title' => 'Neophyte',
        'member_group_id' => IpsApi::MEMBER_GROUPS_NEOPHYTE,
        'discord_role' => DiscordApi::ROLE_NEOPHYTE,
    ];

    public const RANK_PRACTICUS = [
        'title' => 'Practicus',
        'member_group_id' => IpsApi::MEMBER_GROUPS_PRACTICUS,
        'discord_role' => DiscordApi::ROLE_PRACTICUS,
    ];

    public const RANK_ADEPTUS_MINOR = [
        'title' => 'Adeptus Minor',
        'member_group_id' => IpsApi::MEMBER_GROUPS_ADEPTUS_MINOR,
        'discord_role' => DiscordApi::ROLE_ADEPTUS_MINOR,
    ];

    public const RANK_ADEPTUS_MAJOR = [
        'title' => 'Adeptus Major',
        'member_group_id' => IpsApi::MEMBER_GROUPS_ADEPTUS_MAJOR,
        'discord_role' => DiscordApi::ROLE_ADEPTUS_MAJOR,
    ];

    public const RANK_DOMINUS_LIMINIS = [
        'title' => 'Dominus Liminis',
        'member_group_id' => IpsApi::MEMBER_GROUPS_DOMINUS_LIMINIS,
        'discord_role' => DiscordApi::ROLE_DOMINUS_LIMINIS,
    ];

    public const RANK_RECTOR = [
        'title' => 'Rector',
        'member_group_id' => IpsApi::MEMBER_GROUPS_RECTOR,
        'discord_role' => DiscordApi::ROLE_RECTOR,
    ];

    public const RANK_MAGISTER_TEMPLI = [
        'title' => 'Magister Templi',
        'member_group_id' => IpsApi::MEMBER_GROUPS_MAGISTER_TEMPLI,
        'discord_role' => DiscordApi::ROLE_MAGISTER_TEMPLI,
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

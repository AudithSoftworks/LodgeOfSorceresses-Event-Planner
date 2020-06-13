<?php namespace App\Services;

use App\Models\Character;
use App\Models\DpsParse;
use App\Models\User;
use App\Singleton\RoleTypes;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;

class GuildRanksAndClearance
{
    public const RANK_SOULSHRIVEN = [
        'title' => 'Soulshriven',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_SOULSHRIVEN,
        'discordRole' => DiscordApi::ROLE_SOULSHRIVEN,
        'discordShrivenRole' => DiscordApi::ROLE_SOULSHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_INITIATE = [
        'title' => 'Initiate',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_INITIATE,
        'discordShrivenRole' => DiscordApi::ROLE_INITIATE_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_NEOPHYTE = [
        'title' => 'Neophyte',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_NEOPHYTE,
        'discordShrivenRole' => DiscordApi::ROLE_NEOPHYTE_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_PRACTICUS = [
        'title' => 'Practicus',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_PRACTICUS,
        'discordShrivenRole' => DiscordApi::ROLE_PRACTICUS_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_MINOR = [
        'title' => 'Adeptus Minor',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_MINOR,
        'discordShrivenRole' => DiscordApi::ROLE_ADEPTUS_MINOR_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_MAJOR = [
        'title' => 'Adeptus Major',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_MAJOR,
        'discordShrivenRole' => DiscordApi::ROLE_ADEPTUS_MAJOR_SHRIVEN,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_DOMINUS_LIMINIS = [
        'title' => 'Dominus Liminis',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_DOMINUS_LIMINIS,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_ADEPTUS_EXEMPTUS = [
        'title' => 'Adeptus Exemptus',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MEMBERS,
        'discordRole' => DiscordApi::ROLE_ADEPTUS_EXEMPTUS,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => false,
    ];

    public const RANK_MAGISTER_TEMPLI = [
        'title' => 'Magister Templi',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_MAGISTER_TEMPLI,
        'discordRole' => DiscordApi::ROLE_MAGISTER_TEMPLI,
        'discordShrivenRole' => null,
        'isMember' => true,
        'isAdmin' => true,
    ];

    public const RANK_IPSISSIMUS = [
        'title' => 'Magister Templi',
        'ipsGroupId' => IpsApi::MEMBER_GROUP_IPSISSIMUS,
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
            'slug' => 'tier-1',
            'rank' => self::RANK_NEOPHYTE,
        ],
        self::CLEARANCE_TIER_2 => [
            'title' => 'Tier-2 Content',
            'slug' => 'tier-2',
            'rank' => self::RANK_PRACTICUS,
        ],
        self::CLEARANCE_TIER_3 => [
            'title' => 'Tier-3 Content',
            'slug' => 'tier-3',
            'rank' => self::RANK_ADEPTUS_MINOR,
        ],
        self::CLEARANCE_TIER_4 => [
            'title' => 'Tier-4 Content',
            'slug' => 'tier-4',
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
        $character->sets = $characterSets->merge($setsWornDuringParse)->unique()->implode(',');

        $character->save();

        return true;
    }

    /**
     * Persists (while onboarding) or Refreshes user's Discord Roles.
     *
     * @param \App\Models\User $user
     * @param string           $membershipModeInTermsOfDiscordRole
     *
     * @return int
     */
    public function refreshGivenUsersDiscordRoles(User $user, string $membershipModeInTermsOfDiscordRole = DiscordApi::ROLE_SOULSHRIVEN): int
    {
        if (!in_array($membershipModeInTermsOfDiscordRole, [DiscordApi::ROLE_MEMBERS, DiscordApi::ROLE_SOULSHRIVEN], true)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Membership Mode can be [%s|%s]; instead %s provided.',
                    DiscordApi::ROLE_MEMBERS,
                    DiscordApi::ROLE_SOULSHRIVEN,
                    $membershipModeInTermsOfDiscordRole
                )
            );
        }

        /** @var null|\App\Models\UserOAuth $discordAccount */
        $discordAccount = $user->linkedAccounts->firstWhere('remote_provider', 'discord');
        if ($discordAccount === null) {
            throw new UnexpectedValueException(sprintf('User (id: %s) has no Discord account linked?!', $user->id));
        }

        # Discord-Role-IDs for Special ranks
        $usersCurrentDiscordRoles = collect(explode(',', $discordAccount->remote_secondary_groups));
        $discordRoleIdsOfGivenUsersSpecialRoles = $usersCurrentDiscordRoles->intersect([
            DiscordApi::ROLE_MAGISTER_TEMPLI,
            DiscordApi::ROLE_RAID_LEADERS,
            DiscordApi::ROLE_GUIDANCE,
            DiscordApi::ROLE_ADEPTUS_EXEMPTUS,
        ]);

        # Discord-Role-IDs for Character-Roles user cleared for content
        $discordRoleIdsOfGivenUsersRoles = $this->determineRolesGivenUserIsClearedFor($user, true);

        # Discord-Role-IDs for Teams user is active part of
        $discordRoleIdsOfGivenUsersTeams = $this->determineTeamsGivenUserIsActiveMemberOf($user, true);

        # Discord-Role-IDs for user Tier-level
        $clearanceLevel = $this->calculateClearanceLevelOfUser($user);
        if ($clearanceLevel === 0) {
            $discordRoleIdForUserTierLevel = $membershipModeInTermsOfDiscordRole === DiscordApi::ROLE_MEMBERS
                ? DiscordApi::ROLE_INITIATE
                : DiscordApi::ROLE_INITIATE_SHRIVEN;
        } else {
            $discordRoleIdForUserTierLevel = $membershipModeInTermsOfDiscordRole === DiscordApi::ROLE_MEMBERS
                ? self::CLEARANCE_LEVELS[$clearanceLevel]['rank']['discordRole']
                : self::CLEARANCE_LEVELS[$clearanceLevel]['rank']['discordShrivenRole'];
        }

        $usersNewDiscordRoles = collect()
            ->add($membershipModeInTermsOfDiscordRole)
            ->add($discordRoleIdForUserTierLevel)
            ->merge($discordRoleIdsOfGivenUsersSpecialRoles)
            ->merge($discordRoleIdsOfGivenUsersRoles)
            ->merge($discordRoleIdsOfGivenUsersTeams)
            ->unique();

        $result = app('discord.api')->modifyGuildMember($discordAccount->remote_id, ['roles' => $usersNewDiscordRoles->all()]);
        if ($result !== null) {
            $discordAccount->remote_secondary_groups = $usersNewDiscordRoles->implode(',');
            if ($discordAccount->isDirty()) {
                $discordAccount->save();
                Cache::forget('user-' . $user->id);
            }
        }

        return $clearanceLevel;
    }

    /**
     * @param \App\Models\User $user
     * @param bool             $returnDiscordRoleIdsInstead
     *
     * @return iterable|\Illuminate\Support\Collection
     */
    private function determineRolesGivenUserIsClearedFor(User $user, bool $returnDiscordRoleIdsInstead = false): iterable
    {
        $clearedRoles = collect();
        foreach (RoleTypes::ROLES as $roleId => $role) {
            if ($this->determineIfUserHasOtherRankedCharactersWithGivenRole($user, $roleId)) {
                $clearedRoles->add($returnDiscordRoleIdsInstead === false ? $roleId : $role['discordRoleId']);
            }
        }

        return $clearedRoles;
    }

    /**
     * @param \App\Models\User $user
     * @param bool             $returnDiscordRoleIdsInstead
     *
     * @return iterable|\Illuminate\Support\Collection
     */
    private function determineTeamsGivenUserIsActiveMemberOf(User $user, bool $returnDiscordRoleIdsInstead = false): iterable
    {
        $teams = collect();
        foreach ($user->characters as $character) {
            foreach ($character->teams as $team) {
                if ($team->discord_role_id !== null && !$teams->containsStrict('id', $team->id)) {
                    $team->teamMembership->status && $teams->add($returnDiscordRoleIdsInstead === false ? $team : $team->discord_role_id);
                }
            }
        }
        if ($returnDiscordRoleIdsInstead === true && $teams->count() > 0) {
            $teams->add(DiscordApi::ROLE_DOMINUS_LIMINIS);
        }

        return $teams;
    }
}

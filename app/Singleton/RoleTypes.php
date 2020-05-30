<?php namespace App\Singleton;

use App\Services\DiscordApi;

class RoleTypes
{
    public const ROLE_TANK = 1;

    public const ROLE_HEALER = 2;

    public const ROLE_MAGICKA_DD = 3;

    public const ROLE_STAMINA_DD = 4;

    public const ROLES = [
        self::ROLE_TANK => [
            'id' => self::ROLE_TANK,
            'icon' => '🔰',
            'name' => 'Tank',
            'shortName' => 'Tank',
            'discordRoleId' => DiscordApi::ROLE_TANK,
        ],
        self::ROLE_HEALER => [
            'id' => self::ROLE_HEALER,
            'icon' => '⛑',
            'name' => 'Healer',
            'shortName' => 'Healer',
            'discordRoleId' => DiscordApi::ROLE_HEALER,
        ],
        self::ROLE_MAGICKA_DD => [
            'id' => self::ROLE_MAGICKA_DD,
            'icon' => '🔮',
            'name' => 'Damage Dealer (Magicka)',
            'shortName' => 'Magicka DD',
            'discordRoleId' => DiscordApi::ROLE_DAMAGE_DEALER,
        ],
        self::ROLE_STAMINA_DD => [
            'id' => self::ROLE_STAMINA_DD,
            'icon' => '⚔',
            'name' => 'Damage Dealer (Stamina)',
            'shortName' => 'Stamina DD',
            'discordRoleId' => DiscordApi::ROLE_DAMAGE_DEALER,
        ],
    ];

    /**
     * @param int $roleId
     *
     * @return string
     */
    public static function getRoleName(int $roleId): string
    {
        return self::ROLES[$roleId]['name'];
    }

    /**
     * @param int $roleId
     *
     * @return string
     */
    public static function getShortRoleText(int $roleId): string
    {
        return self::ROLES[$roleId]['shortName'];
    }

    /**
     * @param string $roleName
     *
     * @return int
     */
    public static function getRoleId(string $roleName): int
    {
        return array_column(self::ROLES, 'id', 'shortName')[$roleName];
    }

    /**
     * @param int $roleId
     *
     * @return string
     */
    public static function getRoleIcon(int $roleId): string
    {
        return self::ROLES[$roleId]['icon'];
    }
}

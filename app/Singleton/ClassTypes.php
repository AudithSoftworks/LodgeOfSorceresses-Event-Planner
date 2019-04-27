<?php namespace App\Singleton;

class ClassTypes
{
    public const CLASS_DRAGONKNIGHT = 1;

    public const CLASS_NIGHTBLADE = 2;

    public const CLASS_SORCERER = 3;

    public const CLASS_TEMPLAR = 4;

    public const CLASS_WARDEN = 5;

    public const CLASS_NECROMANCER = 6;

    public const TITLE_DRAGONKNIGHT = 'Dragonknight';

    public const TITLE_NIGHTBLADE = 'Nightblade';

    public const TITLE_SORCERER = 'Sorcerer';

    public const TITLE_TEMPLAR = 'Templar';

    public const TITLE_WARDEN = 'Warden';

    public const TITLE_NECROMANCER = 'Necromancer';

    public const CLASSES = [
        self::CLASS_DRAGONKNIGHT => self::TITLE_DRAGONKNIGHT,
        self::CLASS_NIGHTBLADE => self::TITLE_NIGHTBLADE,
        self::CLASS_SORCERER => self::TITLE_SORCERER,
        self::CLASS_TEMPLAR => self::TITLE_TEMPLAR,
        self::CLASS_WARDEN => self::TITLE_WARDEN,
        self::CLASS_NECROMANCER => self::TITLE_NECROMANCER,
    ];

    /**
     * @param string $className
     *
     * @return int
     */
    public static function getClassId(string $className): ?int
    {
        $classId = array_search($className, self::CLASSES, true);

        return $classId ?: null;
    }

    /**
     * @param int $classId
     *
     * @return string
     */
    public static function getClassName(int $classId): string
    {
        return self::CLASSES[$classId] ?? '';
    }
}

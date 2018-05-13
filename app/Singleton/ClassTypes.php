<?php namespace App\Singleton;

class ClassTypes
{
    public const CLASS_DRAGONKNIGHT = 1;

    public const CLASS_NIGHTBLADE = 2;

    public const CLASS_SORCERER = 3;

    public const CLASS_TEMPLAR = 4;

    public const CLASS_WARDEN = 5;

    /**
     * @param string $class_name
     *
     * @return int
     */
    public static function getClassId(string $class_name): int
    {
        switch ($class_name) {
            case 'Dragonknight':
                return self::CLASS_DRAGONKNIGHT;
            case 'Sorcerer':
                return self::CLASS_SORCERER;
            case 'Nightblade':
                return self::CLASS_NIGHTBLADE;
            case 'Warden':
                return self::CLASS_WARDEN;
            case 'Templar':
                return self::CLASS_TEMPLAR;
            default:
                return 0;
        }
    }

    /**
     * @param int $class_id
     *
     * @return string
     */
    public static function getClassName(int $class_id): string
    {
        switch ($class_id) {
            case self::CLASS_DRAGONKNIGHT:
                return 'Dragonknight';
            case self::CLASS_SORCERER:
                return 'Sorcerer';
            case self::CLASS_NIGHTBLADE:
                return 'Nightblade';
            case self::CLASS_WARDEN:
                return 'Warden';
            case self::CLASS_TEMPLAR:
                return 'Templar';
            default:
                return '';
        }
    }
}

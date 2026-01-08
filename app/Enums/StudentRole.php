<?php

namespace App\Enums;

/**
 * Central list of student roles and quotas.
 */
final class StudentRole
{
    public const KM = 'KM';
    public const WAKIL_KM = 'Wakil KM';
    public const SEKERTARIS = 'Sekertaris';
    public const PELAJAR = 'pelajar';

    /**
     * Return all allowed role values.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::KM,
            self::WAKIL_KM,
            self::SEKERTARIS,
            self::PELAJAR,
        ];
    }

    /**
     * Role quotas per class. Null means unlimited.
     *
     * @return array<string,int|null>
     */
    public static function quotas(): array
    {
        return [
            self::KM => 1,
            self::WAKIL_KM => 1,
            self::SEKERTARIS => 2,
            self::PELAJAR => null,
        ];
    }

    /**
     * Validate a role.
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::all(), true);
    }
}

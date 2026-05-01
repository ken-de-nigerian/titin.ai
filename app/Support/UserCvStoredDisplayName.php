<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds a unique stored filename for UI and storage metadata while uploads may repeat the same client name.
 */
final class UserCvStoredDisplayName
{
    public static function uniqueFromClientName(string $clientOriginalName): string
    {
        $clientOriginalName = trim($clientOriginalName);
        if ($clientOriginalName === '') {
            return 'cv_'.Str::lower(Str::random(10)).'.pdf';
        }

        $extension = pathinfo($clientOriginalName, PATHINFO_EXTENSION);
        $basename = pathinfo($clientOriginalName, PATHINFO_FILENAME);
        $basename = self::sanitizeBaseName($basename);
        $random = Str::lower(Str::random(8));
        $ext = $extension !== '' ? '.'.strtolower($extension) : '';

        return "{$basename}_{$random}{$ext}";
    }

    private static function sanitizeBaseName(string $base): string
    {
        $base = str_replace(['/', '\\'], '_', $base);
        $base = preg_replace('/[\x00-\x1F\x7F]/u', '', $base) ?? '';
        $base = trim($base, " \t\n\r\0\x0B.");

        if ($base === '') {
            return 'cv';
        }

        return mb_substr($base, 0, 180);
    }
}

<?php

namespace App\Support;

class MsisdnMasker
{
    public static function mask(?string $phone): string
    {
        if (blank($phone)) {
            return 'Unknown';
        }

        $length = strlen($phone);

        if ($length < 5) {
            return 'Unknown';
        }

        $start = (int) floor(($length - 5) / 2);

        return substr($phone, 0, $start).'*****'.substr($phone, $start + 5);
    }
}

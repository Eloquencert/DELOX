<?php

declare(strict_types=1);

namespace App\Helpers;

class DateHelper
{
    /** Returns a short timestamp string for chat list items (Telegram-style). */
    public static function chatTime(?string $datetime): string
    {
        if (!$datetime) {
            return '';
        }

        $ts  = strtotime($datetime);
        $now = time();

        if (date('Y-m-d', $ts) === date('Y-m-d', $now)) {
            return date('H:i', $ts);
        }

        if (date('Y', $ts) === date('Y', $now)) {
            return date('d M', $ts);
        }

        return date('d.m.y', $ts);
    }
}

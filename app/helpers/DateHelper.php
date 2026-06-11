<?php

namespace App\Helpers;

/**
 * DateHelper
 *
 * Helper class for date/time formatting.
 */
class DateHelper
{
    /**
     * Format a date string
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        $datetime = new \DateTime($date);
        return $datetime->format($format);
    }
}

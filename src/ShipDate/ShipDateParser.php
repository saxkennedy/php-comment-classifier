<?php

declare(strict_types=1);

namespace Sweetwater\ShipDate;

use DateTimeImmutable;

/**
 * Extracts the expected ship date from a comment.
 */
final class ShipDateParser
{
    private const PATTERN = '#Expected Ship Date:\s*(\d{1,2})/(\d{1,2})/(\d{2,4})#i';

    public function parse(string $comment): ?DateTimeImmutable
    {
        if (preg_match(self::PATTERN, $comment, $matches) !== 1) {
            return null;
        }

        $month = (int) $matches[1];
        $day   = (int) $matches[2];
        $year  = (int) $matches[3];

        // Two-digit years in this data are 2000s (e.g. 18 -> 2018).
        if ($year < 100) {
            $year += 2000;
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return (new DateTimeImmutable())->setDate($year, $month, $day)->setTime(0, 0, 0);
    }
}

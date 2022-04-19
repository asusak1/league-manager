<?php


namespace App\Service\Helper;


use App\App\Service\Helper\SplitDateException;
use DateTime;

class DateTimeHelper {


    /**
     * Generates random DateTime in given range
     * Returned DateTime is rounded to 5 min
     * @param DateTime $start
     * @param DateTime $end
     * @return DateTime
     */
    public static function random(DateTime $start, DateTime $end): DateTime {
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new DateTime();
        $randomDate->setTimestamp(((int)($randomTimestamp / 300)) * 300); // round to 5 min
        return $randomDate;
    }

    /**
     * Splits given date range in equal parts
     * @param DateTime $start
     * @param DateTime $end
     * @param int $parts
     * @param int $minInterval in hours
     * @return array
     * @throws SplitDateException if given range is too narrow to split
     */
    public static function splitDates(\DateTime $start, \DateTime $end, int $parts, int $minInterval): array {
        $diffTotal = $end->getTimestamp() - $start->getTimestamp();
        $diff = $diffTotal / ($parts - 1);
        if ($diff < $minInterval * 60 * 60) {
            throw new SplitDateException("Range is too narrow for given minimum interval");
        }
        $dateCollection = [];
        for ($i = 0; $i < $parts; $i++) {
            $dateCollection [] = (new DateTime())->setTimestamp(
                (int)(($start->getTimestamp() + $diff * $i) / 300) * 300); // round to 5 min
        }
        return $dateCollection;
    }

}
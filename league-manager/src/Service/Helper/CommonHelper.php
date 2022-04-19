<?php


namespace App\Service\Helper;


use DateTime;

class CommonHelper {

    /**
     * Checks if given value is in given range
     * @param int|float|DateTime $value
     * @param int|float|DateTime $min
     * @param int|float|DateTime $max
     * @return bool
     */
    public static function isInRange($value, $min, $max): bool {
        return ($min <= $value) && ($value <= $max);
    }

}


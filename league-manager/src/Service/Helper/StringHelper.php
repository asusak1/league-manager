<?php


namespace App\Service\Helper;


class StringHelper {

    /**
     * Generates random string with only letters and whitespace
     * @param int $min minimum length of random string
     * @param int $max maximum length of random string
     * @return string
     */
    public static function random(int $min, int $max): string {
        $chars = "abcdefghijklmnopqrstuvwxyz ";
        $length = mt_rand($min, $max);
        $output = "";
        for ($i = 0; $i < $length; $i++) {
            if ($i === 0 or $i === $length - 1)
                $output .= $chars[mt_rand(0, 25)];
            else
                $output .= $chars[mt_rand(0, 26)];
        }
        return ucwords($output);
    }
}
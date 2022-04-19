<?php


namespace App\Service\Helper;


use App\Entity\Match\Match;
use App\Entity\Sport\Sport;

class MatchHelper {


    /**
     * Computes winning percentage as ratio of won and total played matches
     * @param int $total matches played
     * @param int $wins matches won
     * @return float percentage rounded to 2 decimal spots
     */
    public static function computeWinPerc(int $total, int $wins): float {
        return round(($wins / $total) * 100, 2);
    }


    /**
     * For given winner code returns opposite code
     * Useful when updating away standings
     * @param int $winnerCode
     * @return int
     */
    public static function invertWinnerCode(int $winnerCode): int {
        if ($winnerCode === Match::HOME_WIN) {
            return Match::AWAY_WIN;
        }
        if ($winnerCode === Match::AWAY_WIN) {
            return Match::HOME_WIN;
        }
        return Match::DRAW;
    }

    /**
     * Returns class name of match for specific sport
     * @param Sport $sport
     * @return string
     */
    public static function matchClassForSport(Sport $sport): string {
        return "App\\Entity\\Match\\" . ucfirst($sport->getName()) . "Match";
    }

}
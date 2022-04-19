<?php


namespace App\Service;


use App\Entity\Score\Score;

class ScoreService {

    const MIN_FOOTBALL_SCORE = "0";
    const MAX_FOOTBALL_SCORE = "6";
    const MIN_BASKETBALL_SCORE = "50";
    const MAX_BASKETBALL_SCORE = "120";

    /**
     * Generates random football score
     * @return Score
     */
    public function generateFootballScore(): Score {

        $score = new Score();
        $score->setFinal(mt_rand(self::MIN_FOOTBALL_SCORE, self::MAX_FOOTBALL_SCORE));
        $score->setHalftime(mt_rand(self::MIN_FOOTBALL_SCORE, $score->getFinal()));

        return $score;
    }

    /**
     * Generates random basketball score without overtime
     * @return Score
     */
    public function generateBasketballScore(): Score {

        $score = new Score();
        $score->setPeriod1(floor(mt_rand(self::MIN_BASKETBALL_SCORE, self::MAX_BASKETBALL_SCORE) / 4));
        $score->setPeriod2(floor(mt_rand(self::MIN_BASKETBALL_SCORE, self::MAX_BASKETBALL_SCORE) / 4));
        $score->setPeriod3(floor(mt_rand(self::MIN_BASKETBALL_SCORE, self::MAX_BASKETBALL_SCORE) / 4));
        $score->setPeriod4(floor(mt_rand(self::MIN_BASKETBALL_SCORE, self::MAX_BASKETBALL_SCORE) / 4));
        $score->setFinal($score->getPeriod1() + $score->getPeriod2() + $score->getPeriod3() + $score->getPeriod4());

        return $score;
    }

    /**
     * Generates random overtime score value for two given Score objects
     * @param Score $homeScore
     * @param Score $awayScore
     */
    public function generateBasketballOvertime(Score $homeScore, Score $awayScore) {

        while ($homeScore->getOvertime() === $awayScore->getOvertime()) {
            $homeScore->setOvertime(floor(mt_rand(self::MIN_BASKETBALL_SCORE, self::MAX_BASKETBALL_SCORE) / 8));
            $awayScore->setOvertime(floor(mt_rand(self::MIN_BASKETBALL_SCORE, self::MAX_BASKETBALL_SCORE) / 8));

            $homeScore->setFinal($homeScore->getFinal() + $homeScore->getOvertime());
            $awayScore->setFinal($awayScore->getFinal() + $awayScore->getOvertime());
        }
    }

}
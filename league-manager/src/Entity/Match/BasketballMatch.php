<?php


namespace App\Entity\Match;


use App\App\Entity\Match\InvalidStatusException;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class BasketballMatch
 * @ORM\Entity(repositoryClass=MatchRepository::class)
 */
class BasketballMatch extends Match {

    //status codes
    const FIRST_PERIOD = 3;
    const SECOND_PERIOD = 4;
    const THIRD_PERIOD = 5;
    const FOURTH_PERIOD = 6;
    const OVERTIME = 7;

    /**
     * @param int $status
     * @return Match
     */
    public function setStatus(int $status): Match {
        if ($status < -1 or $status > 7) {
            throw new InvalidStatusException();
        }
        $this->status = $status;

        return $this;
    }

    public function setWinnerCode(): BasketballMatch {
        if (isset($this->status) and $this->getStatus() === self::FINAL_) {

            if ($this->getHomeScore()->getFinal() > $this->getAwayScore()->getFinal()) {
                $this->winnerCode = self::HOME_WIN;
            } else if ($this->getHomeScore()->getFinal() < $this->getAwayScore()->getFinal()) {
                $this->winnerCode = self::AWAY_WIN;
            } else {
                if ($this->getHomeScore()->getOvertime() > $this->getAwayScore()->getOvertime()) {
                    $this->winnerCode = self::HOME_WIN;
                } else {
                    $this->winnerCode = self::AWAY_WIN;
                }
            }
        }

        return $this;
    }
}
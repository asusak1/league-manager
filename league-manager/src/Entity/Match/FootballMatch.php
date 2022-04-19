<?php


namespace App\Entity\Match;


use App\App\Entity\Match\InvalidStatusException;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class FootballMatch
 * @ORM\Entity(repositoryClass=MatchRepository::class)
 */
class FootballMatch extends Match {

    //status codes
    const FIRST_HALF = 3;
    const SECOND_HALF = 4;

    /**
     * @param int $status
     * @return Match
     */

    public function setStatus(int $status): Match {
        if ($status < -1 or $status > 4) {
            throw new InvalidStatusException();
        }
        $this->status = $status;

        return $this;
    }

    public function setWinnerCode(): FootballMatch {
        if (isset($this->status) and $this->getStatus() === self::FINAL_) {
            if ($this->getHomeScore()->getFinal() > $this->getAwayScore()->getFinal()) {
                $this->winnerCode = self::HOME_WIN;
            } else if ($this->getHomeScore()->getFinal() < $this->getAwayScore()->getFinal()) {
                $this->winnerCode = self::AWAY_WIN;
            } else {
                $this->winnerCode = self::DRAW;
            }
        }

        return $this;
    }

}
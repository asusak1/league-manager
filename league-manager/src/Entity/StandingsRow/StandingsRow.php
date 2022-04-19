<?php

namespace App\Entity\StandingsRow;

use App\Entity\Competitor\Competitor;
use App\Entity\Standings\Standings;
use App\Repository\StandingsRowRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=StandingsRowRepository::class)
 */
class StandingsRow {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"common", "standings_row_full"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Competitor::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"standings_row_full"})
     */
    private $competitor;

    /**
     * @ORM\ManyToOne(targetEntity=Standings::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"standings_row_full"})
     */
    private $standings;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     * @Groups({"standings_row_full"})
     */
    private $matches = 0;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     * @Groups({"standings_row_full"})
     */
    private $wins = 0;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     * @Groups({ "standings_row_full"})
     */
    private $losses = 0;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     * @Groups({"standings_row_full"})
     */
    private $scoresFor = 0;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     * @Groups({"standings_row_full"})
     */
    private $scoresAgainst = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"standings_row_full"})
     */
    private $draws = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"standings_row_full"})
     */
    private $points = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"standings_row_full"})
     */
    private $winPercent = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getCompetitor(): ?Competitor {
        return $this->competitor;
    }

    public function setCompetitor(?Competitor $competitor): self {
        $this->competitor = $competitor;

        return $this;
    }

    public function getStandings(): ?Standings {
        return $this->standings;
    }

    public function setStandings(?Standings $standings): self {
        $this->standings = $standings;

        return $this;
    }

    public function getMatches(): ?int {
        return $this->matches;
    }

    public function setMatches(int $matches): self {
        $this->matches = $matches;

        return $this;
    }

    public function getWins(): ?int {
        return $this->wins;
    }

    public function setWins(int $wins): self {
        $this->wins = $wins;

        return $this;
    }

    public function getLosses(): ?int {
        return $this->losses;
    }

    public function setLosses(int $losses): self {
        $this->losses = $losses;

        return $this;
    }

    public function getScoresFor(): ?int {
        return $this->scoresFor;
    }

    public function setScoresFor(int $scoresFor): self {
        $this->scoresFor = $scoresFor;

        return $this;
    }

    public function getScoresAgainst(): ?int {
        return $this->scoresAgainst;
    }

    public function setScoresAgainst(int $scoresAgainst): self {
        $this->scoresAgainst = $scoresAgainst;

        return $this;
    }

    public function getDraws(): ?int {
        return $this->draws;
    }

    public function setDraws(?int $draws): self {
        $this->draws = $draws;

        return $this;
    }

    public function getPoints(): ?int {
        return $this->points;
    }

    public function setPoints(?int $points): self {
        $this->points = $points;

        return $this;
    }

    public function getWinPercent(): ?float {
        return $this->winPercent;
    }

    public function setWinPercent(?float $winPercent): self {
        $this->winPercent = $winPercent;

        return $this;
    }
}

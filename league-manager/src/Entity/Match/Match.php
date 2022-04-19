<?php

namespace App\Entity\Match;

use App\Entity\Competition\Competition;
use App\Entity\Competitor\Competitor;
use App\Entity\Competitor\Team;
use App\Entity\Score\Score;
use App\Entity\Season\Season;
use App\Repository\MatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=MatchRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="sport", type="string")
 * @DiscriminatorMap({"football" = "FootballMatch", "basketball" = "BasketballMatch"})
 */
abstract class Match {
    //winner codes
    const HOME_WIN = 1;
    const AWAY_WIN = 2;
    const DRAW = 3;

    //status codes
    const NOT_STARTED = 0;
    const BREAK_ = 1;
    const FINAL_ = 2;
    const CANCELLED = -1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"common", "match_full"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=Competitor::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"match_full"})
     */
    protected $homeCompetitor;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"match_full"})
     */
    protected $awayCompetitor;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"match_full"})
     */
    protected $startDate;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"match_full"})
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity=Competition::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"match_full"})
     */
    protected $competition;

    /**
     * @ORM\ManyToOne(targetEntity=Season::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"match_full"})
     */
    protected $season;

    /**
     * @ORM\Embedded(class="App\Entity\Score\Score")
     * @Groups({"match_full"})
     */
    protected $homeScore;

    /**
     * @ORM\Embedded(class="App\Entity\Score\Score")
     * @Groups({"match_full"})
     */
    protected $awayScore;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"match_full"})
     */
    protected ?int $winnerCode = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getHomeCompetitor(): ?Competitor {
        return $this->homeCompetitor;
    }

    public function setHomeCompetitor(?Competitor $homeCompetitor): self {
        $this->homeCompetitor = $homeCompetitor;

        return $this;
    }

    public function getAwayCompetitor(): ?Competitor {
        return $this->awayCompetitor;
    }

    public function setAwayCompetitor(?Competitor $awayCompetitor): self {
        $this->awayCompetitor = $awayCompetitor;

        return $this;
    }

    public function getStartDate(): ?\DateTime {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): self {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    public function getCompetition(): ?Competition {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self {
        $this->competition = $competition;

        return $this;
    }

    public function getSeason(): ?Season {
        return $this->season;
    }

    public function setSeason(?Season $season): self {
        $this->season = $season;

        return $this;
    }

    public function getHomeScore(): ?Score {
        return $this->homeScore;
    }

    public function setHomeScore(Score $homeScore): self {
        $this->homeScore = $homeScore;

        return $this;
    }

    public function getAwayScore(): ?Score {
        return $this->awayScore;
    }

    public function setAwayScore(Score $awayScore): self {
        $this->awayScore = $awayScore;

        return $this;
    }

    public function getWinnerCode(): ?int {
        return $this->winnerCode;
    }

    abstract function setWinnerCode(): self;
}

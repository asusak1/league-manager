<?php

namespace App\Entity\Standings;

use App\Entity\Brisi;
use App\Entity\Season\Season;
use App\Entity\StandingsRow\StandingsRow;
use App\Repository\StandingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=StandingsRepository::class)
 */
class Standings {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"common", "standings_full"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Season::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"standings_full"})
     */
    private $season;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"common", "standings_full"})
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=StandingsRow::class, mappedBy="standings")
     */
    private $standingsRows;


    public function __construct() {
        $this->standingsRows = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getSeason(): ?Season {
        return $this->season;
    }

    public function setSeason(?Season $season): self {
        $this->season = $season;

        return $this;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(string $type): self {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|StandingsRow[]
     */
    public function getstandingsRows(): Collection {
        return $this->standingsRows;
    }

    public function addStandingsRow(StandingsRow $standingsRow): self {
        if (!$this->standingsRows->contains($standingsRow)) {
            $this->standingsRows[] = $standingsRow;
            $standingsRow->setStandings($this);
        }

        return $this;
    }

    public function removeStandingsRow(StandingsRow $standingsRow): self {
        if ($this->standingsRows->contains($standingsRow)) {
            $this->standingsRows->removeElement($standingsRow);
            // set the owning side to null (unless already changed)
            if ($standingsRow->getStandings() === $this) {
                $standingsRow->setStandings(null);
            }
        }


    }

}

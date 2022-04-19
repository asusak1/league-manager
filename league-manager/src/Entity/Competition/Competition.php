<?php

namespace App\Entity\Competition;

use App\Entity\Category\Category;
use App\Entity\Season\Season;
use App\Repository\CompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CompetitionRepository::class)
 */
class Competition {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="name must not be blank")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="slug must not be blank")
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="competitions")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="category must not be null")
     */
    private $category;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="matchesAgainst must not be blank")
     */
    private $matchesAgainst;

    /**
     * @ORM\OneToMany(targetEntity=Season::class, mappedBy="competition", orphanRemoval=true)
     */
    private $seasons;

    public function __construct() {
        $this->seasons = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setSlug(?string $slug): self {
        $this->slug = $slug;

        return $this;
    }

    public function getCategory(): ?Category {
        return $this->category;
    }

    public function setCategory(?Category $category): self {
        $this->category = $category;

        return $this;
    }

    public function getMatchesAgainst(): ?int {
        return $this->matchesAgainst;
    }

    public function setMatchesAgainst($matchesAgainst): self {
        $this->matchesAgainst = $matchesAgainst;

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection {
        return $this->seasons;
    }

    public function addSeason(Season $season): self {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setCompetition($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self {
        if ($this->seasons->contains($season)) {
            $this->seasons->removeElement($season);
            // set the owning side to null (unless already changed)
            if ($season->getCompetition() === $this) {
                $season->setCompetition(null);
            }
        }

        return $this;
    }
}

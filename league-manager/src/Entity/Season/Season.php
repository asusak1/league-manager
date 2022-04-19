<?php

namespace App\Entity\Season;

use App\Entity\Competition\Competition;
use App\Repository\SeasonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SeasonRepository::class)
 */
class Season {
    const MIN_LENGTH = 7;
    const MAX_LENGTH = 11;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"common", "season_full"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"common", "season_full"})
     * @Assert\NotBlank(message="name must not be blank")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Competition::class, inversedBy="seasons")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"season_full"})
     * @Assert\NotNull(message="competition must not be null")
     */
    private $competition;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"season_full"})
     * @Assert\NotNull(message="startDate must not be null")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"season_full"})
     * @Assert\NotNull(message="endDate must not be null")
     */
    private $endDate;

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

    public function getCompetition(): ?Competition {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self {
        $this->competition = $competition;

        return $this;
    }

    public function getStartDate(): ?\DateTime {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): self {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): self {
        $this->endDate = $endDate;

        return $this;
    }
}

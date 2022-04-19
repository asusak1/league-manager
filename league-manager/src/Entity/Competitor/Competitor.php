<?php

namespace App\Entity\Competitor;

use App\Entity\Country\Country;
use App\Entity\Sport\Sport;
use App\Repository\CompetitorRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=CompetitorRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"team" = "Team", "person" = "Person", "pair" = "Pair"})
 */
abstract class Competitor {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"common", "competitor_full"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"common", "competitor_full"})
     * @Assert\NotBlank(message="name must not be blank")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"common", "competitor_full"})
     * @Assert\NotNull
     */
    protected $slug;

    /**
     * @ORM\ManyToOne(targetEntity=Sport::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"competitor_full"})
     * @Assert\NotBlank
     */
    protected $sport;

    /**
     * @ORM\Embedded(class="App\Entity\Country\Country")
     * @Groups({"competitor_full"})
     * @Assert\NotBlank
     */
    protected Country $country;

    public function __construct() {
        $this->country = new Country();
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

    public function getSport(): ?Sport {
        return $this->sport;
    }

    public function setSport(?Sport $sport): self {
        $this->sport = $sport;

        return $this;
    }

    public function getCountry(): Country {
        return $this->country;
    }

    public function setCountry(Country $country): self {
        $this->country = $country;

        return $this;
    }
}

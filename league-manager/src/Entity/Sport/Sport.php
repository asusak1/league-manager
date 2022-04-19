<?php

namespace App\Entity\Sport;

use App\Repository\SportRepository;
use Ausi\SlugGenerator\SlugGenerator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SportRepository::class)
 */
class Sport {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"common"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"common"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"common"})
     */
    private string $slug;

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setSlug(string $slug): self {
        $this->slug = $slug;

        return $this;
    }
}

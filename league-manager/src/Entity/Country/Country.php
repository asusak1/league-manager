<?php

namespace App\Entity\Country;

use Doctrine\ORM\Mapping as ORM;
use League\ISO3166\ISO3166;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Embeddable
 */
class Country {
    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Groups({"common", "country_full"})
     * @Assert\NotBlank(message="ISO  must not be blank")
     * @Assert\Length(min = 2, max = 2)
     */
    private ?string $ISO = null;

    public function getISO(): ?string {
        return $this->ISO;
    }

    public function setISO(?string $ISO): self {
        $this->ISO = $ISO;

        return $this;
    }

    /**
     * @Groups({"country_full"})
     */
    public function getName(): ?string {
        if ($this->ISO)
            return (new ISO3166())->alpha2($this->ISO)["name"];
        else
            throw new \Exception("ISO not set");
    }
}

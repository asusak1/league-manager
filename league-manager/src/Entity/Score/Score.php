<?php

namespace App\Entity\Score;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Embeddable
 */
class Score {
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $halftime = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $period1 = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $period2 = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $period3 = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $period4 = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $final = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"common"})
     */
    private $overtime = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getHalftime(): ?int {
        return $this->halftime;
    }

    public function setHalftime(int $halftime): self {
        $this->halftime = $halftime;

        return $this;
    }

    public function getPeriod1(): ?int {
        return $this->period1;
    }

    public function setPeriod1(int $period1): self {
        $this->period1 = $period1;

        return $this;
    }

    public function getPeriod2(): ?int {
        return $this->period2;
    }

    public function setPeriod2(int $period2): self {
        $this->period2 = $period2;

        return $this;
    }

    public function getPeriod3(): ?int {
        return $this->period3;
    }

    public function setPeriod3(int $period3): self {
        $this->period3 = $period3;

        return $this;
    }

    public function getPeriod4(): ?int {
        return $this->period4;
    }

    public function setPeriod4(int $period4): self {
        $this->period4 = $period4;

        return $this;
    }

    public function getFinal(): ?int {
        return $this->final;
    }

    public function setFinal(int $final): self {
        $this->final = $final;

        return $this;
    }

    public function getOvertime(): ?int {
        return $this->overtime;
    }

    public function setOvertime(int $overtime): self {
        $this->overtime = $overtime;

        return $this;
    }
}

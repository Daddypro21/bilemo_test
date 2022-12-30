<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ConfigurationRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(["getProduct"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le champ color est obligatoire")]
    #[Groups(["getProduct"])]
    private ?string $color = null;

    #[ORM\Column]
    #[Groups(["getProduct"])]
    #[Assert\NotBlank(message:"Le champ price est obligatoire")]
    private ?float $price = null;

    #[ORM\Column]
    #[Groups(["getProduct"])]
    #[Assert\NotBlank(message:"Le champ memory est obligatoire")]
    private ?float $memory = null;

    #[ORM\ManyToOne(inversedBy: 'configurations')]
    private ?Product $product = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getMemory(): ?float
    {
        return $this->memory;
    }

    public function setMemory(float $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
class Provider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\Column(length: 255, unique: true)]
    #[SerializedName("internal_key")]
    private string $internalKey;

    #[ORM\Column]
    #[SerializedName("has_discount")]
    private bool $hasDiscount = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getInternalKey(): string
    {
        return $this->internalKey;
    }

    public function setInternalKey(string $internalKey): static
    {
        $this->internalKey = $internalKey;

        return $this;
    }

    #[SerializedName("has_discount")]
    public function isHasDiscount(): ?bool
    {
        return $this->hasDiscount;
    }

    public function setHasDiscount(bool $hasDiscount): static
    {
        $this->hasDiscount = $hasDiscount;

        return $this;
    }
}

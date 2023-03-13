<?php

namespace App\Entity;

use App\Repository\PayPalOrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PayPalOrderRepository::class)]
class PayPalOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $paypal_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tracking_number = null;

    #[ORM\Column(nullable: true)]
    private ?bool $finalized = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getPaypalId(): ?string
    {
        return $this->paypal_id;
    }

    public function setPaypalId(string $paypal_id): self
    {
        $this->paypal_id = $paypal_id;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->tracking_number;
    }

    public function setTrackingNumber(?string $tracking_number): self
    {
        $this->tracking_number = $tracking_number;

        return $this;
    }

    public function isFinalized(): ?bool
    {
        return $this->finalized;
    }

    public function setFinalized(?bool $finalized): self
    {
        $this->finalized = $finalized;

        return $this;
    }

}

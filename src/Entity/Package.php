<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PackageRepository::class)
 */
class Package
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $boxId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shippingCompany;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trackingNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proof;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packinguserId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $shippingUserId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getBoxId(): ?string
    {
        return $this->boxId;
    }

    public function setBoxId(string $boxId): self
    {
        $this->boxId = $boxId;

        return $this;
    }

    public function getShippingCompany(): ?string
    {
        return $this->shippingCompany;
    }

    public function setShippingCompany(string $shippingCompany): self
    {
        $this->shippingCompany = $shippingCompany;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): self
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getProof(): ?string
    {
        return $this->proof;
    }

    public function setProof(?string $proof): self
    {
        $this->proof = $proof;

        return $this;
    }

    public function getPackinguserId(): ?int
    {
        return $this->packinguserId;
    }

    public function setPackinguserId(?int $packinguserId): self
    {
        $this->packinguserId = $packinguserId;

        return $this;
    }

    public function getShippingUserId(): ?int
    {
        return $this->shippingUserId;
    }

    public function setShippingUserId(?int $shippingUserId): self
    {
        $this->shippingUserId = $shippingUserId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

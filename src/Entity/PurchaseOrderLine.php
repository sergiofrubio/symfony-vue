<?php

namespace App\Entity;

use App\Repository\PurchaseOrderLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseOrderLineRepository::class)]
class PurchaseOrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $subtotal = null;

    #[ORM\ManyToOne(inversedBy: 'purchaseOrderLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PurchaseOrder $purchaseOrder = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getPurchaseOrder(): ?PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(?PurchaseOrder $purchaseOrder): static
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}

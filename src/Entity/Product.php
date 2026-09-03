<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Contract\TenantAwareInterface;
use App\Entity\Traits\TenantTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class Product implements TenantAwareInterface
{
    use TimestampableTrait;
    use TenantTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $sku = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    private ?string $costPrice = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '21.00'])]
    private ?string $taxRate = '21.00'; // IVA en porcentaje

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $stockQuantity = 0;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->price = '0.00';
        $this->costPrice = '0.00';
        $this->taxRate = '21.00';
        $this->stockQuantity = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCostPrice(): ?string
    {
        return $this->costPrice;
    }

    public function setCostPrice(?string $costPrice): static
    {
        $this->costPrice = $costPrice;

        return $this;
    }

    public function getTaxRate(): ?string
    {
        return $this->taxRate;
    }

    public function setTaxRate(?string $taxRate): static
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}

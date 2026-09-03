<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Contract\TenantAwareInterface;
use App\Entity\Traits\TenantTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\PurchaseOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseOrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class PurchaseOrder implements TenantAwareInterface
{
    use TimestampableTrait;
    use TenantTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $orderNumber = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expectedDeliveryDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $subtotal = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $taxAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $totalAmount = '0.00';

    #[ORM\Column(length: 30, options: ['default' => 'draft'])]
    private ?string $status = 'draft'; // draft, ordered, received, cancelled

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'purchaseOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    /**
     * @var Collection<int, PurchaseOrderLine>
     */
    #[ORM\OneToMany(targetEntity: PurchaseOrderLine::class, mappedBy: 'purchaseOrder', orphanRemoval: true, cascade: ['persist'])]
    private Collection $purchaseOrderLines;

    public function __construct()
    {
        $this->purchaseOrderLines = new ArrayCollection();
        $this->date = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->status = 'draft';
        $this->subtotal = '0.00';
        $this->taxAmount = '0.00';
        $this->totalAmount = '0.00';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getExpectedDeliveryDate(): ?\DateTimeInterface
    {
        return $this->expectedDeliveryDate;
    }

    public function setExpectedDeliveryDate(?\DateTimeInterface $expectedDeliveryDate): static
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;

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

    public function getTaxAmount(): ?string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return Collection<int, PurchaseOrderLine>
     */
    public function getPurchaseOrderLines(): Collection
    {
        return $this->purchaseOrderLines;
    }

    public function addPurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine): static
    {
        if (!$this->purchaseOrderLines->contains($purchaseOrderLine)) {
            $this->purchaseOrderLines->add($purchaseOrderLine);
            $purchaseOrderLine->setPurchaseOrder($this);
        }

        return $this;
    }

    public function removePurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine): static
    {
        if ($this->purchaseOrderLines->removeElement($purchaseOrderLine)) {
            if ($purchaseOrderLine->getPurchaseOrder() === $this) {
                $purchaseOrderLine->setPurchaseOrder(null);
            }
        }

        return $this;
    }

    public function calculateTotals(): void
    {
        $sub = 0.0;
        $tax = 0.0;

        foreach ($this->purchaseOrderLines as $line) {
            $lineSub = (float)$line->getQuantity() * (float)$line->getUnitPrice();
            $lineTax = $lineSub * ((float)($line->getTaxRate() ?? 21.0) / 100.0);
            $sub += $lineSub;
            $tax += $lineTax;
            $line->setSubtotal(number_format($lineSub, 2, '.', ''));
        }

        $this->subtotal = number_format($sub, 2, '.', '');
        $this->taxAmount = number_format($tax, 2, '.', '');
        $this->totalAmount = number_format($sub + $tax, 2, '.', '');
    }
}

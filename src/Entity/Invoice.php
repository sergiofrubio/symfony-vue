<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Contract\TenantAwareInterface;
use App\Entity\Traits\TenantTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class Invoice implements TenantAwareInterface
{
    use TimestampableTrait;
    use TenantTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $number = null;

    #[ORM\Column(length: 20, options: ['default' => 'A'])]
    private ?string $series = 'A';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $subtotal = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $taxAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $totalAmount = '0.00';

    #[ORM\Column(length: 30, options: ['default' => 'draft'])]
    private ?string $status = 'draft'; // draft, validated, sent, paid, cancelled

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    /**
     * @var Collection<int, InvoiceLine>
     */
    #[ORM\OneToMany(targetEntity: InvoiceLine::class, mappedBy: 'invoice', orphanRemoval: true, cascade: ['persist'])]
    private Collection $invoiceLines;

    public function __construct()
    {
        $this->invoiceLines = new ArrayCollection();
        $this->date = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->status = 'draft';
        $this->series = 'A';
        $this->subtotal = '0.00';
        $this->taxAmount = '0.00';
        $this->totalAmount = '0.00';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(string $series): static
    {
        $this->series = $series;

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

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;

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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceLine>
     */
    public function getInvoiceLines(): Collection
    {
        return $this->invoiceLines;
    }

    public function addInvoiceLine(InvoiceLine $invoiceLine): static
    {
        if (!$this->invoiceLines->contains($invoiceLine)) {
            $this->invoiceLines->add($invoiceLine);
            $invoiceLine->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceLine(InvoiceLine $invoiceLine): static
    {
        if ($this->invoiceLines->removeElement($invoiceLine)) {
            if ($invoiceLine->getInvoice() === $this) {
                $invoiceLine->setInvoice(null);
            }
        }

        return $this;
    }

    public function calculateTotals(): void
    {
        $sub = 0.0;
        $tax = 0.0;

        foreach ($this->invoiceLines as $line) {
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

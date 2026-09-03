<?php

namespace App\Entity\Traits;

use App\Entity\Company;
use Doctrine\ORM\Mapping as ORM;

trait TenantTrait
{
    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}

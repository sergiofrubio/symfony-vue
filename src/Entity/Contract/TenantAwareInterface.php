<?php

namespace App\Entity\Contract;

use App\Entity\Company;

interface TenantAwareInterface
{
    public function getCompany(): ?Company;
    public function setCompany(?Company $company): static;
}

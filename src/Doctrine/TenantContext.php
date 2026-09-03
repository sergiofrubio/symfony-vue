<?php

namespace App\Doctrine;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TenantContext
{
    private ?Company $currentCompany = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {}

    public function getCurrentCompany(): ?Company
    {
        return $this->currentCompany;
    }

    public function setCurrentCompany(?Company $company): void
    {
        $this->currentCompany = $company;

        if ($company && $company->getId()) {
            if (!$this->em->getFilters()->isEnabled('tenant_filter')) {
                $this->em->getFilters()->enable('tenant_filter');
            }
            $this->em->getFilters()->getFilter('tenant_filter')->setParameter('company_id', $company->getId());
        } else {
            if ($this->em->getFilters()->isEnabled('tenant_filter')) {
                $this->em->getFilters()->disable('tenant_filter');
            }
        }
    }
}

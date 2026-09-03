<?php

namespace App\Doctrine;

use App\Entity\Contract\TenantAwareInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Comprobar si la entidad implementa TenantAwareInterface
        if (!$targetEntity->reflClass || !$targetEntity->reflClass->implementsInterface(TenantAwareInterface::class)) {
            return '';
        }

        try {
            $companyId = $this->getParameter('company_id');
        } catch (\InvalidArgumentException $e) {
            return '';
        }

        if (empty($companyId)) {
            return '';
        }

        return sprintf('%s.company_id = %s', $targetTableAlias, $companyId);
    }
}

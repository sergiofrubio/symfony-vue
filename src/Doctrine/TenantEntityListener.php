<?php

namespace App\Doctrine;

use App\Entity\AuditLog;
use App\Entity\Contract\TenantAwareInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist, priority: 500)]
class TenantEntityListener
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        // Si es una entidad multi-empresa y no tiene asignada empresa todavía, asignar la actual
        if ($entity instanceof TenantAwareInterface && $entity->getCompany() === null) {
            $currentCompany = $this->tenantContext->getCurrentCompany();
            if ($currentCompany) {
                $entity->setCompany($currentCompany);
            }
        }
    }
}

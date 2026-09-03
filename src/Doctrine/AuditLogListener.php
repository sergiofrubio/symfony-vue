<?php

namespace App\Doctrine;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsDoctrineListener(event: Events::onFlush)]
class AuditLogListener
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly TenantContext $tenantContext
    ) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $user = $this->security->getUser();
        $userEmail = $user instanceof User ? $user->getEmail() : ($user ? $user->getUserIdentifier() : 'system');
        $request = $this->requestStack->getCurrentRequest();
        $ip = $request ? $request->getClientIp() : null;
        $company = $this->tenantContext->getCurrentCompany();

        // Inserts
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof AuditLog) {
                continue;
            }

            $audit = new AuditLog();
            $audit->setEntityClass(get_class($entity));
            $audit->setEntityId(method_exists($entity, 'getId') ? (string)$entity->getId() : 'new');
            $audit->setAction('INSERT');
            $audit->setUserEmail($userEmail);
            $audit->setIpAddress($ip);
            $audit->setCompany($company);

            $em->persist($audit);
            $uow->computeChangeSet($em->getClassMetadata(AuditLog::class), $audit);
        }

        // Updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof AuditLog) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($entity);
            // Filtramos campos sensibles como password
            unset($changeSet['password']);

            if (empty($changeSet)) {
                continue;
            }

            $formattedChanges = [];
            foreach ($changeSet as $field => [$old, $new]) {
                $formattedChanges[$field] = [
                    'old' => is_object($old) && method_exists($old, 'getId') ? $old->getId() : (is_scalar($old) ? $old : null),
                    'new' => is_object($new) && method_exists($new, 'getId') ? $new->getId() : (is_scalar($new) ? $new : null),
                ];
            }

            $audit = new AuditLog();
            $audit->setEntityClass(get_class($entity));
            $audit->setEntityId(method_exists($entity, 'getId') ? (string)$entity->getId() : 'unknown');
            $audit->setAction('UPDATE');
            $audit->setChanges($formattedChanges);
            $audit->setUserEmail($userEmail);
            $audit->setIpAddress($ip);
            $audit->setCompany($company);

            $em->persist($audit);
            $uow->computeChangeSet($em->getClassMetadata(AuditLog::class), $audit);
        }

        // Deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof AuditLog) {
                continue;
            }

            $audit = new AuditLog();
            $audit->setEntityClass(get_class($entity));
            $audit->setEntityId(method_exists($entity, 'getId') ? (string)$entity->getId() : 'deleted');
            $audit->setAction('DELETE');
            $audit->setUserEmail($userEmail);
            $audit->setIpAddress($ip);
            $audit->setCompany($company);

            $em->persist($audit);
            $uow->computeChangeSet($em->getClassMetadata(AuditLog::class), $audit);
        }
    }
}

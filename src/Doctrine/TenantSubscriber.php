<?php

namespace App\Doctrine;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TenantSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $companyHeader = $request->headers->get('X-Company-Id');

        if ($companyHeader) {
            $company = $this->em->getRepository(Company::class)->find((int)$companyHeader);
            if ($company) {
                $this->tenantContext->setCurrentCompany($company);
                return;
            }
        }

        // Si no se envía header, usar la defaultCompany del usuario autenticado si existe
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $defaultCompany = $user->getDefaultCompany();
            if ($defaultCompany) {
                $this->tenantContext->setCurrentCompany($defaultCompany);
                return;
            }

            // O la primera empresa que tenga asignada
            if ($user->getCompanies()->count() > 0) {
                $this->tenantContext->setCurrentCompany($user->getCompanies()->first());
            }
        }
    }
}

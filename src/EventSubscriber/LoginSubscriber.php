<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        $user->setLastLogin(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();
    }
}

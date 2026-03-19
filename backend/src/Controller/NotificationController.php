<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends AbstractController
{
    #[Route('/api/notifications', name: 'api_notifications', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        if ($user) {
            $data = [
                ['id' => 1, 'title' => 'Nueva orden recibida', 'body' => 'Orden #123', 'date' => $now->format(\DateTime::ATOM), 'read' => false, 'url' => '/orders/123'],
                ['id' => 2, 'title' => 'Backup completado', 'date' => $now->modify('-1 hour')->format(\DateTime::ATOM), 'read' => true],
            ];
        } else {
            $data = [
                ['id' => 1, 'title' => 'Bienvenido', 'date' => $now->format(\DateTime::ATOM), 'read' => true],
            ];
        }

        return new JsonResponse($data);
    }
}

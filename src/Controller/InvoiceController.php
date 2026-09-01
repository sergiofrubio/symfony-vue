<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/invoices', name: 'api_invoices_')]
class InvoiceController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $repo = $this->em->getRepository(Invoice::class);

        $search = trim((string) $request->query->get('search', ''));
        $status = trim((string) $request->query->get('status', ''));
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $qb = $repo->createQueryBuilder('i')
            ->leftJoin('i.customer', 'c')
            ->addSelect('c');

        if ($search !== '') {
            $qb->andWhere('i.number LIKE :s OR c.name LIKE :s')
               ->setParameter('s', '%'.$search.'%');
        }

        if ($status !== '') {
            $qb->andWhere('i.status = :st')->setParameter('st', $status);
        }

        // Si no es admin, limitar a facturas cuyo cliente tenga el mismo email que el usuario
        $user = $this->getUser();
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // intentar filtrar por email del cliente
            $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            if ($email) {
                $qb->andWhere('c.email = :uemail')->setParameter('uemail', $email);
            }
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(i.id)')->getQuery()->getSingleScalarResult();

        $qb->orderBy('i.id', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        $items = array_map(function (Invoice $inv) {
            return [
                'id' => $inv->getId(),
                'number' => $inv->getNumber(),
                'date' => $inv->getDate() ? $inv->getDate()->format(\DateTime::ATOM) : null,
                'totalAmount' => $inv->getTotalAmount(),
                'status' => $inv->getStatus(),
                'customer' => $inv->getCustomer() ? [ 'id' => $inv->getCustomer()->getId(), 'name' => $inv->getCustomer()->getName(), 'email' => $inv->getCustomer()->getEmail() ] : null,
            ];
        }, $qb->getQuery()->getResult());

        return new JsonResponse([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getInvoice(int $id): JsonResponse
    {
        $repo = $this->em->getRepository(Invoice::class);
        $inv = $repo->find($id);
        if (!$inv) return new JsonResponse(['error' => 'Not found'], 404);

        $user = $this->getUser();
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            if ($email && $inv->getCustomer() && $inv->getCustomer()->getEmail() !== $email) {
                return new JsonResponse(['error' => 'Forbidden'], 403);
            }
        }

        $lines = array_map(function (InvoiceLine $l) {
            return [
                'id' => $l->getId(),
                'product' => $l->getProduct() ? ['id' => $l->getProduct()->getId(), 'name' => $l->getProduct()->getName()] : null,
                'quantity' => $l->getQuantity(),
                'unitPrice' => $l->getUnitPrice(),
                'subtotal' => $l->getSubtotal(),
            ];
        }, $inv->getInvoiceLines()->toArray());

        return new JsonResponse([
            'id' => $inv->getId(),
            'number' => $inv->getNumber(),
            'date' => $inv->getDate() ? $inv->getDate()->format(\DateTime::ATOM) : null,
            'totalAmount' => $inv->getTotalAmount(),
            'status' => $inv->getStatus(),
            'customer' => $inv->getCustomer() ? ['id' => $inv->getCustomer()->getId(), 'name' => $inv->getCustomer()->getName(), 'email' => $inv->getCustomer()->getEmail()] : null,
            'lines' => $lines,
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];

        $number = trim((string) ($data['number'] ?? ''));
        $customerId = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
        if ($number === '' || !$customerId) {
            return new JsonResponse(['error' => 'number and customer_id required'], 400);
        }

        $customer = $this->em->getRepository(Customer::class)->find($customerId);
        if (!$customer) return new JsonResponse(['error' => 'Customer not found'], 400);

        // Autorizar: si no admin, solo permitir si customer.email == user.email
        $user = $this->getUser();
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            if ($email && $customer->getEmail() !== $email) {
                return new JsonResponse(['error' => 'Forbidden'], 403);
            }
        }

        $inv = new Invoice();
        $inv->setNumber($number);
        if (!empty($data['date'])) {
            try { $inv->setDate(new \DateTime($data['date'])); } catch (\Exception $e) { }
        }
        $inv->setStatus($data['status'] ?? 'draft');
        $inv->setCustomer($customer);

        $total = 0.0;
        if (!empty($data['lines']) && is_array($data['lines'])) {
            foreach ($data['lines'] as $ln) {
                $line = new InvoiceLine();
                $line->setQuantity((string)($ln['quantity'] ?? '0'));
                $line->setUnitPrice((string)($ln['unitPrice'] ?? '0'));
                $line->setSubtotal((string)($ln['subtotal'] ?? '0'));
                $inv->addInvoiceLine($line);
            }
            foreach ($inv->getInvoiceLines() as $l) { $total += (float)$l->getSubtotal(); }
        }

        $inv->setTotalAmount(number_format($total, 2, '.', ''));

        try {
            $this->em->persist($inv);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to create invoice', 'details' => $e->getMessage()], 400);
        }

        return new JsonResponse(['id' => $inv->getId()], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $repo = $this->em->getRepository(Invoice::class);
        $inv = $repo->find($id);
        if (!$inv) return new JsonResponse(['error' => 'Not found'], 404);

        $user = $this->getUser();
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            if ($email && $inv->getCustomer() && $inv->getCustomer()->getEmail() !== $email) {
                return new JsonResponse(['error' => 'Forbidden'], 403);
            }
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];
        if (isset($data['status'])) $inv->setStatus((string)$data['status']);
        if (!empty($data['date'])) { try { $inv->setDate(new \DateTime($data['date'])); } catch (\Exception $e) { } }

        // manejar líneas si vienen
        if (isset($data['lines']) && is_array($data['lines'])) {
            // limpiar y reañadir
            foreach ($inv->getInvoiceLines()->toArray() as $existing) {
                $inv->removeInvoiceLine($existing);
                $this->em->remove($existing);
            }
            $total = 0.0;
            foreach ($data['lines'] as $ln) {
                $line = new InvoiceLine();
                $line->setQuantity((string)($ln['quantity'] ?? '0'));
                $line->setUnitPrice((string)($ln['unitPrice'] ?? '0'));
                $line->setSubtotal((string)($ln['subtotal'] ?? '0'));
                $inv->addInvoiceLine($line);
                $total += (float)$line->getSubtotal();
            }
            $inv->setTotalAmount(number_format($total, 2, '.', ''));
        }

        try {
            $this->em->persist($inv);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to update invoice', 'details' => $e->getMessage()], 400);
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $repo = $this->em->getRepository(Invoice::class);
        $inv = $repo->find($id);
        if (!$inv) return new JsonResponse(['error' => 'Not found'], 404);

        $user = $this->getUser();
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            if ($email && $inv->getCustomer() && $inv->getCustomer()->getEmail() !== $email) {
                return new JsonResponse(['error' => 'Forbidden'], 403);
            }
        }

        try {
            $this->em->remove($inv);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to delete invoice', 'details' => $e->getMessage()], 400);
        }

        return new JsonResponse(['success' => true]);
    }

    // Generar PDF simple: si Dompdf está presente devuelve application/pdf, si no devuelve HTML
    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(int $id): Response
    {
        $repo = $this->em->getRepository(Invoice::class);
        $inv = $repo->find($id);
        if (!$inv) return new Response('Not found', 404);

        $user = $this->getUser();
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            if ($email && $inv->getCustomer() && $inv->getCustomer()->getEmail() !== $email) {
                return new Response('Forbidden', 403);
            }
        }

        $html = $this->renderView('invoices/pdf.html.twig', ['invoice' => $inv]);

//         if (class_exists('\Dompdf\Dompdf')) {
//             $dompdf = new \Dompdf\Dompdf();
//             $dompdf->loadHtml($html);
//             $dompdf->setPaper('A4', 'portrait');
//             $dompdf->render();
//             $pdf = $dompdf->output();
//             return new Response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="invoice_'.$inv->getNumber().'.pdf"']);
//         }

        // Sin dompdf, devolver HTML y una cabecera informativa
        return new Response($html, 200, ['Content-Type' => 'text/html', 'X-PDF-Available' => 'false']);
    }
}

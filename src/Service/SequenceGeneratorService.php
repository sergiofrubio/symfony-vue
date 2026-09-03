<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Entity\PurchaseOrder;
use Doctrine\ORM\EntityManagerInterface;

class SequenceGeneratorService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * Genera un número correlativo anual sin huecos por empresa y serie (ej: A2026-0001 o PO-2026-0001)
     */
    public function getNextInvoiceNumber(?Company $company, string $series = 'A'): string
    {
        $year = (new \DateTime())->format('Y');

        $qb = $this->em->createQueryBuilder();
        $qb->select('i.number')
            ->from(Invoice::class, 'i')
            ->where('i.series = :series')
            ->andWhere('i.number LIKE :pattern')
            ->setParameter('series', $series)
            ->setParameter('pattern', $series . $year . '-%')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1);

        if ($company) {
            $qb->andWhere('i.company = :company')
               ->setParameter('company', $company);
        }

        $lastNumber = $qb->getQuery()->getOneOrNullResult();

        $nextSeq = 1;
        if ($lastNumber && isset($lastNumber['number'])) {
            $parts = explode('-', $lastNumber['number']);
            if (count($parts) >= 2) {
                $nextSeq = ((int)end($parts)) + 1;
            }
        }

        return sprintf('%s%s-%04d', $series, $year, $nextSeq);
    }

    public function getNextPurchaseOrderNumber(?Company $company): string
    {
        $year = (new \DateTime())->format('Y');

        $qb = $this->em->createQueryBuilder();
        $qb->select('po.orderNumber')
            ->from(PurchaseOrder::class, 'po')
            ->where('po.orderNumber LIKE :pattern')
            ->setParameter('pattern', 'PO' . $year . '-%')
            ->orderBy('po.id', 'DESC')
            ->setMaxResults(1);

        if ($company) {
            $qb->andWhere('po.company = :company')
               ->setParameter('company', $company);
        }

        $lastNumber = $qb->getQuery()->getOneOrNullResult();

        $nextSeq = 1;
        if ($lastNumber && isset($lastNumber['orderNumber'])) {
            $parts = explode('-', $lastNumber['orderNumber']);
            if (count($parts) >= 2) {
                $nextSeq = ((int)end($parts)) + 1;
            }
        }

        return sprintf('PO%s-%04d', $year, $nextSeq);
    }
}

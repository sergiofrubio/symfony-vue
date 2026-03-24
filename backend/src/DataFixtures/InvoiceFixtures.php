<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Payment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InvoiceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Clientes
        $c1 = new Customer();
        $c1->setName('Acme S.A.');
        $c1->setEmail('cliente1@example.com');
        $c1->setPhone('600111222');
        $c1->setAddress('Calle Falsa 123');
        $c1->setTaxId('A12345678');

        $c2 = new Customer();
        $c2->setName('Beta Ltd');
        $c2->setEmail('cliente2@example.com');
        $c2->setPhone('600333444');
        $c2->setAddress('Av. Real 10');
        $c2->setTaxId('B87654321');

        $manager->persist($c1);
        $manager->persist($c2);

        // Productos
        $p1 = new Product();
        $p1->setName('Producto A');
        $p1->setSku('PROD-A');
        $p1->setPrice('19.90');
        $p1->setStockQuantity(100);

        $p2 = new Product();
        $p2->setName('Servicio B');
        $p2->setSku('SERV-B');
        $p2->setPrice('99.00');
        $p2->setStockQuantity(0);

        $p3 = new Product();
        $p3->setName('Licencia C');
        $p3->setSku('LIC-C');
        $p3->setPrice('250.00');
        $p3->setStockQuantity(50);

        $manager->persist($p1);
        $manager->persist($p2);
        $manager->persist($p3);

        // Factura 1
        $inv1 = new Invoice();
        $inv1->setNumber('INV-1000');
        $inv1->setStatus('sent');
        $inv1->setCustomer($c1);
        $inv1->setDate(new \DateTime('-10 days'));

        $l1 = new InvoiceLine();
        $l1->setProduct($p1);
        $l1->setQuantity('2');
        $l1->setUnitPrice($p1->getPrice());
        $l1->setSubtotal(number_format((float)$p1->getPrice() * 2, 2, '.', ''));
        $inv1->addInvoiceLine($l1);

        $l2 = new InvoiceLine();
        $l2->setProduct($p2);
        $l2->setQuantity('1');
        $l2->setUnitPrice($p2->getPrice());
        $l2->setSubtotal(number_format((float)$p2->getPrice() * 1, 2, '.', ''));
        $inv1->addInvoiceLine($l2);

        $total1 = 0.0;
        foreach ($inv1->getInvoiceLines() as $ln) { $total1 += (float)$ln->getSubtotal(); }
        $inv1->setTotalAmount(number_format($total1, 2, '.', ''));

        $manager->persist($inv1);

        // Pago para factura 1
        $pay1 = new Payment();
        $pay1->setInvoice($inv1);
        $pay1->setAmount($inv1->getTotalAmount());
        $pay1->setMethod('transfer');
        $pay1->setReference('TRX-1000');
        $pay1->setDate(new \DateTime('-5 days'));
        $manager->persist($pay1);

        // Factura 2 (parcial)
        $inv2 = new Invoice();
        $inv2->setNumber('INV-1001');
        $inv2->setStatus('draft');
        $inv2->setCustomer($c2);
        $inv2->setDate(new \DateTime('-2 days'));

        $l3 = new InvoiceLine();
        $l3->setProduct($p3);
        $l3->setQuantity('3');
        $l3->setUnitPrice($p3->getPrice());
        $l3->setSubtotal(number_format((float)$p3->getPrice() * 3, 2, '.', ''));
        $inv2->addInvoiceLine($l3);

        $total2 = 0.0;
        foreach ($inv2->getInvoiceLines() as $ln) { $total2 += (float)$ln->getSubtotal(); }
        $inv2->setTotalAmount(number_format($total2, 2, '.', ''));

        $manager->persist($inv2);

        $manager->flush();
    }
}

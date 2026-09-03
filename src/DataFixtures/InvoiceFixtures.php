<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['taxId' => 'B12345678']);
        $customer = $manager->getRepository(Customer::class)->findOneBy(['company' => $company]);
        $product = $manager->getRepository(Product::class)->findOneBy(['company' => $company]);

        if (!$company || !$customer || !$product) {
            return;
        }

        // Factura de demostración
        $invoice = new Invoice();
        $invoice->setCompany($company);
        $invoice->setSeries('A');
        $invoice->setNumber('A2026-0001');
        $invoice->setCustomer($customer);
        $invoice->setDate(new \DateTime());
        $invoice->setDueDate(new \DateTime('+30 days'));
        $invoice->setStatus('sent');
        $invoice->setNotes('Factura emitida bajo plantilla ERP estándar');

        $line = new InvoiceLine();
        $line->setProduct($product);
        $line->setDescription($product->getName());
        $line->setQuantity('10.00');
        $line->setUnitPrice($product->getPrice());
        $line->setTaxRate($product->getTaxRate());
        $line->setSubtotal(number_format(10.0 * (float)$product->getPrice(), 2, '.', ''));
        $invoice->addInvoiceLine($line);

        $invoice->calculateTotals();
        $manager->persist($invoice);

        $manager->flush();
    }
}

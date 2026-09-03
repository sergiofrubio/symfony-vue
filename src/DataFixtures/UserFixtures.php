<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Permission;
use App\Entity\Product;
use App\Entity\Role;
use App\Entity\Supplier;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // 1. Crear Empresas Demo
        $company1 = new Company();
        $company1->setName('Acme Consulting S.L.');
        $company1->setTaxId('B12345678');
        $company1->setEmail('info@acmeconsulting.com');
        $company1->setPhone('+34 910 000 111');
        $company1->setAddress('Paseo de la Castellana 100, Madrid');
        $company1->setCurrency('EUR');
        $manager->persist($company1);

        $company2 = new Company();
        $company2->setName('Tech Innovations S.A.');
        $company2->setTaxId('A87654321');
        $company2->setEmail('contact@techinnovations.com');
        $company2->setPhone('+34 930 111 222');
        $company2->setAddress('Avinguda Diagonal 200, Barcelona');
        $company2->setCurrency('EUR');
        $manager->persist($company2);

        // 2. Crear Permisos Base
        $permissionCodes = [
            'customer.read' => ['Ver Clientes', 'CRM'],
            'customer.create' => ['Crear Clientes', 'CRM'],
            'customer.edit' => ['Editar Clientes', 'CRM'],
            'customer.delete' => ['Eliminar Clientes', 'CRM'],
            'supplier.read' => ['Ver Proveedores', 'Compras'],
            'supplier.create' => ['Crear Proveedores', 'Compras'],
            'supplier.edit' => ['Editar Proveedores', 'Compras'],
            'product.read' => ['Ver Productos', 'Catálogo'],
            'product.create' => ['Crear Productos', 'Catálogo'],
            'product.edit' => ['Editar Productos', 'Catálogo'],
            'invoice.read' => ['Ver Facturas', 'Facturación'],
            'invoice.create' => ['Crear Facturas', 'Facturación'],
            'invoice.validate' => ['Validar Facturas', 'Facturación'],
            'purchase.read' => ['Ver Pedidos Compra', 'Compras'],
            'purchase.create' => ['Crear Pedidos Compra', 'Compras'],
        ];

        $permissions = [];
        foreach ($permissionCodes as $code => [$name, $category]) {
            $perm = new Permission();
            $perm->setCode($code);
            $perm->setName($name);
            $perm->setCategory($category);
            $manager->persist($perm);
            $permissions[$code] = $perm;
        }

        // 3. Crear Roles
        $roleUser = new Role();
        $roleUser->setName('User');
        $roleUser->setSlug('ROLE_USER');
        $roleUser->addPermission($permissions['customer.read']);
        $roleUser->addPermission($permissions['product.read']);
        $roleUser->addPermission($permissions['invoice.read']);
        $manager->persist($roleUser);

        $roleAdmin = new Role();
        $roleAdmin->setName('Admin');
        $roleAdmin->setSlug('ROLE_ADMIN');
        foreach ($permissions as $perm) {
            $roleAdmin->addPermission($perm);
        }
        $manager->persist($roleAdmin);

        // 4. Crear Usuarios
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setIsActive(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->addRole($roleAdmin);
        $admin->addCompany($company1);
        $admin->addCompany($company2);
        $admin->setDefaultCompany($company1);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setIsActive(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
        $user->addRole($roleUser);
        $user->addCompany($company1);
        $user->setDefaultCompany($company1);
        $manager->persist($user);

        // 5. Clientes y Proveedores de prueba vinculados a Empresa
        $customer1 = new Customer();
        $customer1->setName('Inversiones Globales S.A.');
        $customer1->setEmail('administracion@inversiones.es');
        $customer1->setPhone('+34 912 345 678');
        $customer1->setTaxId('B99887766');
        $customer1->setAddress('Gran Vía 45, Madrid');
        $customer1->setCompany($company1);
        $manager->persist($customer1);

        $customer2 = new Customer();
        $customer2->setName('Comercial Mediterránea S.L.');
        $customer2->setEmail('contacto@comercialmed.es');
        $customer2->setPhone('+34 963 001 122');
        $customer2->setTaxId('B55443322');
        $customer2->setAddress('Calle Colón 12, Valencia');
        $customer2->setCompany($company2);
        $manager->persist($customer2);

        $supplier1 = new Supplier();
        $supplier1->setName('Servicios Cloud Iberia');
        $supplier1->setEmail('facturacion@cloudiberia.com');
        $supplier1->setTaxId('B11223344');
        $supplier1->setPhone('+34 900 100 200');
        $supplier1->setCompany($company1);
        $manager->persist($supplier1);

        // 6. Productos
        $prod1 = new Product();
        $prod1->setName('Consultoría Tecnológica Senior (hora)');
        $prod1->setSku('CONS-SR-01');
        $prod1->setPrice('75.00');
        $prod1->setCostPrice('40.00');
        $prod1->setTaxRate('21.00');
        $prod1->setStockQuantity(999);
        $prod1->setCompany($company1);
        $manager->persist($prod1);

        $prod2 = new Product();
        $prod2->setName('Licencia Anual ERP Starter');
        $prod2->setSku('LIC-ERP-01');
        $prod2->setPrice('1200.00');
        $prod2->setCostPrice('200.00');
        $prod2->setTaxRate('21.00');
        $prod2->setStockQuantity(100);
        $prod2->setCompany($company1);
        $manager->persist($prod2);

        $manager->flush();
    }
}

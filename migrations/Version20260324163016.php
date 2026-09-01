<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260324163016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, tax_id VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, number VARCHAR(20) NOT NULL, date DATETIME NOT NULL, total_amount NUMERIC(12, 2) NOT NULL, status VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_9065174496901F54 (number), INDEX IDX_906517449395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_line (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, product_id INT NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(12, 2) NOT NULL, subtotal NUMERIC(12, 2) NOT NULL, INDEX IDX_D3D1D6932989F1FD (invoice_id), INDEX IDX_D3D1D6934584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, date DATETIME NOT NULL, amount NUMERIC(12, 2) NOT NULL, method VARCHAR(50) NOT NULL, reference VARCHAR(255) DEFAULT NULL, INDEX IDX_6D28840D2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, sku VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, stock_quantity INT NOT NULL, UNIQUE INDEX UNIQ_D34A04ADF9038C4 (sku), INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_2FB3D0EE9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_order (id INT AUTO_INCREMENT NOT NULL, supplier_id INT NOT NULL, order_number VARCHAR(20) NOT NULL, date DATETIME NOT NULL, total_amount NUMERIC(12, 2) NOT NULL, status VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_21E210B2551F0F81 (order_number), INDEX IDX_21E210B22ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_order_line (id INT AUTO_INCREMENT NOT NULL, purchase_order_id INT NOT NULL, product_id INT NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(12, 2) NOT NULL, subtotal NUMERIC(12, 2) NOT NULL, INDEX IDX_90D6D92BA45D7E6A (purchase_order_id), INDEX IDX_90D6D92B4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, tax_id VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, assigned_to_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, priority VARCHAR(20) NOT NULL, due_date DATE DEFAULT NULL, INDEX IDX_527EDB25166D1F9C (project_id), INDEX IDX_527EDB25F4BD7827 (assigned_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D6934584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE purchase_order ADD CONSTRAINT FK_21E210B22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE purchase_order_line ADD CONSTRAINT FK_90D6D92BA45D7E6A FOREIGN KEY (purchase_order_id) REFERENCES purchase_order (id)');
        $this->addSql('ALTER TABLE purchase_order_line ADD CONSTRAINT FK_90D6D92B4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449395C3F3');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D6932989F1FD');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D6934584665A');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2989F1FD');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE9395C3F3');
        $this->addSql('ALTER TABLE purchase_order DROP FOREIGN KEY FK_21E210B22ADD6D8C');
        $this->addSql('ALTER TABLE purchase_order_line DROP FOREIGN KEY FK_90D6D92BA45D7E6A');
        $this->addSql('ALTER TABLE purchase_order_line DROP FOREIGN KEY FK_90D6D92B4584665A');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25166D1F9C');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F4BD7827');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE purchase_order');
        $this->addSql('DROP TABLE purchase_order_line');
        $this->addSql('DROP TABLE supplier');
        $this->addSql('DROP TABLE task');
    }
}

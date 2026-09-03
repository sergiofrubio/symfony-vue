<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903164459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, entity_class VARCHAR(255) NOT NULL, entity_id VARCHAR(100) NOT NULL, action VARCHAR(50) NOT NULL, changes JSON DEFAULT NULL, user_email VARCHAR(180) DEFAULT NULL, ip_address VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_F6E1C0F5979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, tax_id VARCHAR(50) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, currency VARCHAR(3) DEFAULT \'EUR\' NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4FBF094FB2A824D8 (tax_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(100) NOT NULL, category VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_E04992AA77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_permission (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_6F7DF886D60322AC (role_id), INDEX IDX_6F7DF886FED90CCA (permission_id), PRIMARY KEY(role_id, permission_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_company (user_id INT NOT NULL, company_id INT NOT NULL, INDEX IDX_17B21745A76ED395 (user_id), INDEX IDX_17B21745979B1AD6 (company_id), PRIMARY KEY(user_id, company_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_company ADD CONSTRAINT FK_17B21745A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_company ADD CONSTRAINT FK_17B21745979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer ADD company_id INT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_81398E09979B1AD6 ON customer (company_id)');
        $this->addSql('DROP INDEX UNIQ_9065174496901F54 ON invoice');
        $this->addSql('ALTER TABLE invoice ADD company_id INT DEFAULT NULL, ADD series VARCHAR(20) DEFAULT \'A\' NOT NULL, ADD due_date DATETIME DEFAULT NULL, ADD subtotal NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, ADD tax_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE number number VARCHAR(50) DEFAULT NULL, CHANGE total_amount total_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, CHANGE status status VARCHAR(30) DEFAULT \'draft\' NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_90651744979B1AD6 ON invoice (company_id)');
        $this->addSql('ALTER TABLE invoice_line ADD tax_rate NUMERIC(5, 2) DEFAULT \'21.00\' NOT NULL, ADD description VARCHAR(255) DEFAULT NULL, CHANGE product_id product_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_D34A04ADF9038C4 ON product');
        $this->addSql('ALTER TABLE product ADD company_id INT DEFAULT NULL, ADD cost_price NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, ADD tax_rate NUMERIC(5, 2) DEFAULT \'21.00\' NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD979B1AD6 ON product (company_id)');
        $this->addSql('ALTER TABLE project ADD company_id INT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE979B1AD6 ON project (company_id)');
        $this->addSql('DROP INDEX UNIQ_21E210B2551F0F81 ON purchase_order');
        $this->addSql('ALTER TABLE purchase_order ADD company_id INT DEFAULT NULL, ADD expected_delivery_date DATETIME DEFAULT NULL, ADD subtotal NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, ADD tax_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE order_number order_number VARCHAR(50) DEFAULT NULL, CHANGE total_amount total_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, CHANGE status status VARCHAR(30) DEFAULT \'draft\' NOT NULL');
        $this->addSql('ALTER TABLE purchase_order ADD CONSTRAINT FK_21E210B2979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_21E210B2979B1AD6 ON purchase_order (company_id)');
        $this->addSql('ALTER TABLE purchase_order_line ADD tax_rate NUMERIC(5, 2) DEFAULT \'21.00\' NOT NULL, ADD description VARCHAR(255) DEFAULT NULL, CHANGE product_id product_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A989D9B62 ON role (slug)');
        $this->addSql('ALTER TABLE supplier ADD company_id INT DEFAULT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE supplier ADD CONSTRAINT FK_9B2A6C7E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_9B2A6C7E979B1AD6 ON supplier (company_id)');
        $this->addSql('ALTER TABLE user ADD default_company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DFB84EB5 FOREIGN KEY (default_company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649DFB84EB5 ON user (default_company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09979B1AD6');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744979B1AD6');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE979B1AD6');
        $this->addSql('ALTER TABLE purchase_order DROP FOREIGN KEY FK_21E210B2979B1AD6');
        $this->addSql('ALTER TABLE supplier DROP FOREIGN KEY FK_9B2A6C7E979B1AD6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649DFB84EB5');
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F5979B1AD6');
        $this->addSql('ALTER TABLE role_permission DROP FOREIGN KEY FK_6F7DF886D60322AC');
        $this->addSql('ALTER TABLE role_permission DROP FOREIGN KEY FK_6F7DF886FED90CCA');
        $this->addSql('ALTER TABLE user_company DROP FOREIGN KEY FK_17B21745A76ED395');
        $this->addSql('ALTER TABLE user_company DROP FOREIGN KEY FK_17B21745979B1AD6');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE role_permission');
        $this->addSql('DROP TABLE user_company');
        $this->addSql('DROP INDEX IDX_D34A04AD979B1AD6 ON product');
        $this->addSql('ALTER TABLE product DROP company_id, DROP cost_price, DROP tax_rate, DROP created_at, DROP updated_at');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADF9038C4 ON product (sku)');
        $this->addSql('DROP INDEX IDX_2FB3D0EE979B1AD6 ON project');
        $this->addSql('ALTER TABLE project DROP company_id, DROP created_at, DROP updated_at');
        $this->addSql('DROP INDEX IDX_21E210B2979B1AD6 ON purchase_order');
        $this->addSql('ALTER TABLE purchase_order DROP company_id, DROP expected_delivery_date, DROP subtotal, DROP tax_amount, DROP notes, DROP created_at, DROP updated_at, CHANGE order_number order_number VARCHAR(20) NOT NULL, CHANGE total_amount total_amount NUMERIC(12, 2) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_21E210B2551F0F81 ON purchase_order (order_number)');
        $this->addSql('ALTER TABLE invoice_line DROP tax_rate, DROP description, CHANGE product_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE purchase_order_line DROP tax_rate, DROP description, CHANGE product_id product_id INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_57698A6A989D9B62 ON role');
        $this->addSql('DROP INDEX IDX_90651744979B1AD6 ON invoice');
        $this->addSql('ALTER TABLE invoice DROP company_id, DROP series, DROP due_date, DROP subtotal, DROP tax_amount, DROP notes, DROP created_at, DROP updated_at, CHANGE number number VARCHAR(20) NOT NULL, CHANGE total_amount total_amount NUMERIC(12, 2) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9065174496901F54 ON invoice (number)');
        $this->addSql('DROP INDEX IDX_9B2A6C7E979B1AD6 ON supplier');
        $this->addSql('ALTER TABLE supplier DROP company_id, DROP created_at, DROP updated_at');
        $this->addSql('DROP INDEX IDX_8D93D649DFB84EB5 ON user');
        $this->addSql('ALTER TABLE user DROP default_company_id');
        $this->addSql('DROP INDEX IDX_81398E09979B1AD6 ON customer');
        $this->addSql('ALTER TABLE customer DROP company_id, DROP created_at, DROP updated_at');
    }
}

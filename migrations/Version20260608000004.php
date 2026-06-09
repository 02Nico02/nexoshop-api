<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add orders and order items tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, order_number VARCHAR(30) DEFAULT NULL, customer_name VARCHAR(150) NOT NULL, customer_email VARCHAR(180) NOT NULL, customer_phone VARCHAR(50) NOT NULL, shipping_street VARCHAR(150) NOT NULL, shipping_city VARCHAR(100) NOT NULL, shipping_province VARCHAR(100) NOT NULL, shipping_postcode VARCHAR(30) NOT NULL, shipping_reference VARCHAR(255) DEFAULT NULL, shipping_method_code VARCHAR(50) NOT NULL, shipping_method_label VARCHAR(150) NOT NULL, shipping_cost INT NOT NULL, payment_method_code VARCHAR(50) NOT NULL, payment_method_label VARCHAR(150) NOT NULL, subtotal INT NOT NULL, discount_total INT NOT NULL, tax_total INT NOT NULL, total INT NOT NULL, status VARCHAR(30) NOT NULL DEFAULT \'created\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_E52FFDEB94A4C7D4 (order_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, product_id INT DEFAULT NULL, variant_id INT DEFAULT NULL, product_name VARCHAR(160) NOT NULL, variant_description VARCHAR(255) DEFAULT NULL, unit_price INT NOT NULL, original_unit_price INT NOT NULL, discount_percentage SMALLINT DEFAULT NULL, tax_percentage SMALLINT NOT NULL, quantity INT NOT NULL, discount_total INT NOT NULL, tax_total INT NOT NULL, subtotal INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C20C35FCA76ED395 (order_id), INDEX IDX_C20C35FCD34A04AD (product_id), INDEX IDX_C20C35FC8601F5D8 (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_C20C35FCA76ED395 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_C20C35FCD34A04AD FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_C20C35FC8601F5D8 FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_C20C35FCA76ED395');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_C20C35FCD34A04AD');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_C20C35FC8601F5D8');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
    }
}

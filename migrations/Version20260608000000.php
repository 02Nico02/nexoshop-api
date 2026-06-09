<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create categories and products tables for the NexoShop catalog base.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, sort_order INT DEFAULT 0 NOT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_497DD634989D9B62 (slug), INDEX IDX_497DD634727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, short_description VARCHAR(255) DEFAULT NULL, base_price INT NOT NULL, original_price INT DEFAULT NULL, discount_percentage SMALLINT DEFAULT NULL, discount_amount INT DEFAULT NULL, tax_percentage SMALLINT NOT NULL DEFAULT 21, tax_label VARCHAR(50) NOT NULL DEFAULT \'IVA\', tax_included TINYINT(1) NOT NULL DEFAULT 0, image VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL DEFAULT 1, featured TINYINT(1) NOT NULL DEFAULT 0, stock INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_B3BA5A5A989D9B62 (slug), INDEX IDX_B3BA5A5A12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_497DD634727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_497DD634727ACA70');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE categories');
    }
}

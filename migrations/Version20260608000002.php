<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product variants, images, features and attributes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_variants (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, sku VARCHAR(80) NOT NULL, options JSON NOT NULL, stock INT DEFAULT 0 NOT NULL, price_delta INT DEFAULT 0 NOT NULL, image VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4CB2A4CBA15E4E73 (sku), INDEX IDX_4CB2A4CB4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_images (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, variant_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, main TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7917CC6D4584665A (product_id), INDEX IDX_7917CC6DE9376F74 (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_features (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, name VARCHAR(150) NOT NULL, value VARCHAR(255) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B9B1C4DB4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attributes (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, name VARCHAR(150) NOT NULL, value VARCHAR(255) NOT NULL, filterable TINYINT(1) DEFAULT 0 NOT NULL, facet_group VARCHAR(100) DEFAULT NULL, sort_order INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2AD87CC14584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_variants ADD CONSTRAINT FK_4CB2A4CB4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT FK_7917CC6D4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT FK_7917CC6DE9376F74 FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product_features ADD CONSTRAINT FK_B9B1C4DB4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT FK_2AD87CC14584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_7917CC6DE9376F74');
        $this->addSql('ALTER TABLE product_variants DROP FOREIGN KEY FK_4CB2A4CB4584665A');
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_7917CC6D4584665A');
        $this->addSql('ALTER TABLE product_features DROP FOREIGN KEY FK_B9B1C4DB4584665A');
        $this->addSql('ALTER TABLE product_attributes DROP FOREIGN KEY FK_2AD87CC14584665A');
        $this->addSql('DROP TABLE product_attributes');
        $this->addSql('DROP TABLE product_features');
        $this->addSql('DROP TABLE product_images');
        $this->addSql('DROP TABLE product_variants');
    }
}

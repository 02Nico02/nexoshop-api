<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shipping and payment methods tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shipping_methods (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, cost INT DEFAULT 0 NOT NULL, eta VARCHAR(100) DEFAULT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, sort_order INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_57F6B29977153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_methods (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(50) NOT NULL, requires_card_data TINYINT(1) DEFAULT 0 NOT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, sort_order INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_B1C4D2DA77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE payment_methods');
        $this->addSql('DROP TABLE shipping_methods');
    }
}

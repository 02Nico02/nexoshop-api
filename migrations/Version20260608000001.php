<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category level for hierarchical navigation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE categories ADD level INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE categories SET level = 0 WHERE parent_id IS NULL');
        $this->addSql('UPDATE categories SET level = 1 WHERE parent_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE categories DROP COLUMN level');
    }
}

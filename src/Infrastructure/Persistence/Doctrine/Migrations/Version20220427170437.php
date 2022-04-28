<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220427170437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add column on account_preference, to allow distinguishing strategy parameters';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account_preference ADD type VARCHAR(32)');

        $this->addSql('ALTER TABLE account_preference DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE account_preference ADD CONSTRAINT PRIMARY KEY (account_id, name, type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account_preference DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE account_preference ADD CONSTRAINT PRIMARY KEY (account_id, name)');

        $this->addSql('ALTER TABLE account_preference DROP type');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

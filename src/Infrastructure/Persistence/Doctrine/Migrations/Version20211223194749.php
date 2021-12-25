<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211223194749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Account Preferences';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE account_preference (
                account_id INT NOT NULL,
                name VARCHAR(32) NOT NULL,
                value VARCHAR(64) NOT NULL,
                INDEX idx_account_id (account_id),
                PRIMARY KEY(account_id, name)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account_preference ADD CONSTRAINT fk_account_preference_account_id FOREIGN KEY (account_id) REFERENCES account(id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE account_preference');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

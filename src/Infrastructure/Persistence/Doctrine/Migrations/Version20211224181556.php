<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211224181556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add staking asset table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE staking_asset (
                id INT AUTO_INCREMENT PRIMARY KEY,
                asset_id INT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                symbol VARCHAR(16) NOT NULL,
                min_reward DOUBLE PRECISION NOT NULL,
                max_reward DOUBLE PRECISION NOT NULL,
                min_staking DOUBLE PRECISION NOT NULL,
                min_unstaking DOUBLE PRECISION NOT NULL,
                on_chain TINYINT(1) NOT NULL,
                can_stake TINYINT(1) NOT NULL,
                can_unstake TINYINT(1) NOT NULL,
                method VARCHAR(32) NOT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                UNIQUE INDEX uniq_idx_symbol (symbol),
                UNIQUE INDEX uniq_idx_method (method),
                UNIQUE INDEX uniq_idx_asset_id (asset_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE staking_asset ADD CONSTRAINT fk_staking_asset_asset_id FOREIGN KEY (asset_id) REFERENCES asset (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE staking_asset');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

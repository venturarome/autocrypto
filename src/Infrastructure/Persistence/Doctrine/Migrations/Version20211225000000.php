<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20211225000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split assets in spot and staking + create transactions table + other needed changes';
    }

    public function up(Schema $schema): void
    {
        // Asset -> SpotAsset
        $this->addSql('ALTER TABLE asset DROP created_at, DROP updated_at, DROP deleted_at;');
        $this->addSql('RENAME TABLE asset TO spot_asset;');

        $this->addSql('ALTER TABLE asset_pair DROP created_at, DROP updated_at, DROP deleted_at;');
        $this->addSql('RENAME TABLE asset_pair TO spot_pair;');
        $this->addSql('RENAME TABLE asset_pair_leverage TO spot_pair_leverage;');

        // StakingAsset
        $this->addSql('ALTER TABLE staking_asset RENAME COLUMN asset_id TO spot_asset_id;');
        $this->addSql('ALTER TABLE staking_asset ADD display_decimals INT NOT NULL DEFAULT 0 AFTER symbol;');
        $this->addSql('ALTER TABLE staking_asset ADD decimals INT NOT NULL DEFAULT 0 AFTER symbol;');

        // Balance
        $this->addSql('ALTER TABLE asset_balance DROP created_at, DROP updated_at, DROP deleted_at;');
        $this->addSql('ALTER TABLE asset_balance ADD type VARCHAR(8) NOT NULL AFTER account_id;');
        $this->addSql('ALTER TABLE asset_balance DROP FOREIGN KEY fk_asset_balance_asset_id;');
        $this->addSql('RENAME TABLE asset_balance TO account_balance;');

        $this->addSql('
            CREATE TABLE transaction (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                reference VARCHAR(32) NOT NULL,
                balance_id INT DEFAULT NULL,
                type VARCHAR(8) NOT NULL,
                operation VARCHAR(16) NOT NULL,
                operation_reference VARCHAR(32) NOT NULL,
                timestamp DOUBLE PRECISION NOT NULL,
                amount DOUBLE PRECISION NOT NULL,
                fee DOUBLE PRECISION NOT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                UNIQUE INDEX uniq_idx_reference (reference),
                INDEX idx_balance_id (balance_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT fk_transaction_account_balance_id FOREIGN KEY (balance_id) REFERENCES account_balance(id);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE transaction');

        $this->addSql('RENAME TABLE account_balance TO asset_balance;');
        $this->addSql('ALTER TABLE asset_balance ADD CONSTRAINT fk_asset_balance_asset_id FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset_balance DROP type;');
        $this->addSql('ALTER TABLE asset_balance ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL;');

        $this->addSql('ALTER TABLE asset_balance DROP display_decimals, DROP decimals;');
        $this->addSql('ALTER TABLE staking_asset RENAME COLUMN spot_asset_id TO asset_id;');

        $this->addSql('RENAME TABLE spot_pair_leverage TO asset_pair_leverage;');
        $this->addSql('RENAME TABLE spot_pair TO asset_pair;');
        $this->addSql('ALTER TABLE asset_pair ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL;');

        $this->addSql('RENAME TABLE spot_asset TO asset;');
        $this->addSql('ALTER TABLE asset ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL;');
    }


    public function isTransactional(): bool
    {
        return false;
    }
}

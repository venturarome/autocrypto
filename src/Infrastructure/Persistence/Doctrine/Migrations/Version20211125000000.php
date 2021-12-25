<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211125000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'First migration. Created from mappings and modified slightly';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE account (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                reference VARCHAR(8) NOT NULL,
                status VARCHAR(16) NOT NULL,
                api_key VARCHAR(64) NOT NULL,
                secret_key VARCHAR(128) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME DEFAULT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                UNIQUE INDEX uniq_idx_reference (reference)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('
            CREATE TABLE asset (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                symbol VARCHAR(8) NOT NULL,
                name VARCHAR(32) DEFAULT NULL,
                decimals INT NOT NULL,
                display_decimals INT NOT NULL,
                type VARCHAR(8) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME DEFAULT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                UNIQUE INDEX uniq_idx_symbol (symbol)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('
            CREATE TABLE asset_pair (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                symbol VARCHAR(16) NOT NULL,
                base_id INT DEFAULT NULL,
                quote_id INT DEFAULT NULL,
                decimals INT NOT NULL,
                vol_decimals INT NOT NULL,
                order_min DOUBLE NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME DEFAULT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                UNIQUE INDEX uniq_idx_symbol (symbol),
                INDEX idx_base_id (base_id),
                INDEX idx_quote_id (quote_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('
            CREATE TABLE asset_pair_leverage (
                pair_id INT DEFAULT NULL,
                operation VARCHAR(8) NOT NULL,
                value INT NOT NULL,
                INDEX idx_asset_pair_id (pair_id),
                PRIMARY KEY (pair_id, operation, value)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('
            CREATE TABLE asset_balance (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                account_id INT DEFAULT NULL,
                asset_id INT DEFAULT NULL,
                amount DOUBLE NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME DEFAULT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                INDEX idx_account_id (account_id),
                INDEX idx_asset_id (asset_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('
            CREATE TABLE event (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                name VARCHAR(64) NOT NULL,
                entity_uuid VARCHAR(36) NOT NULL,
                content JSON NOT NULL,
                thrown_at DATETIME NOT NULL,
                handler_status VARCHAR(16) DEFAULT NULL,
                processed_at DATETIME DEFAULT NULL,
                UNIQUE INDEX uniq_idx_uuid (uuid),
                INDEX idx_entity_uuid (entity_uuid),
                CHECK (JSON_VALID(content))
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset_pair ADD CONSTRAINT fk_asset_pair_base_id FOREIGN KEY (base_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset_pair ADD CONSTRAINT fk_asset_pair_quote_id FOREIGN KEY (quote_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset_pair_leverage ADD CONSTRAINT fk_asset_pair_leverage_pair_id FOREIGN KEY (pair_id) REFERENCES asset_pair (id)');
        $this->addSql('ALTER TABLE asset_balance ADD CONSTRAINT fk_asset_balance_account_id FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE asset_balance ADD CONSTRAINT fk_asset_balance_asset_id FOREIGN KEY (asset_id) REFERENCES asset (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_pair DROP FOREIGN KEY fk_asset_pair_base_id');
        $this->addSql('ALTER TABLE asset_pair DROP FOREIGN KEY fk_asset_pair_quote_id');
        $this->addSql('ALTER TABLE asset_balance DROP FOREIGN KEY fk_asset_balance_asset_id');
        $this->addSql('ALTER TABLE asset_balance DROP FOREIGN KEY fk_asset_balance_account_id');
        $this->addSql('ALTER TABLE asset_pair_leverage DROP FOREIGN KEY fk_asset_pair_leverage_pair_id');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE asset_pair');
        $this->addSql('DROP TABLE asset_pair_leverage');
        $this->addSql('DROP TABLE asset_balance');
        $this->addSql('DROP TABLE event');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

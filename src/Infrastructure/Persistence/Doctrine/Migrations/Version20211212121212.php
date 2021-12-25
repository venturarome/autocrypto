<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20211212121212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Second migration. Created candle and order tables from mappings and modified slightly';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE candle (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pair_id INT DEFAULT NULL,
                timespan INT NOT NULL,
                timestamp INT NOT NULL,
                open DOUBLE NOT NULL,
                high DOUBLE NOT NULL,
                low DOUBLE NOT NULL,
                close DOUBLE NOT NULL,
                volume DOUBLE NOT NULL,
                trades INT NOT NULL,
                INDEX idx_pair_id (pair_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candle ADD CONSTRAINT fk_candle_pair_id FOREIGN KEY (pair_id) REFERENCES asset_pair (id)');

        $this->addSql('
            CREATE TABLE `order` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(36) NOT NULL,
                account_id INT DEFAULT NULL,
                pair_id INT DEFAULT NULL,
                type VARCHAR(32) NOT NULL,
                operation VARCHAR(8) NOT NULL,
                volume DOUBLE NOT NULL,
                trigger_price DOUBLE,
                limit_price DOUBLE,
                UNIQUE INDEX uniq_idx_uuid (uuid)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT fk_order_account_id FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT fk_order_pair_id FOREIGN KEY (pair_id) REFERENCES asset_pair (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE candle');
        $this->addSql('DROP TABLE `order`');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

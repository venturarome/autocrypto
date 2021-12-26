<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20211226000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add transaction::price';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ADD price FLOAT DEFAULT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction DROP price;');
    }


    public function isTransactional(): bool
    {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20220404000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add compounded key on candle table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candle ADD UNIQUE uniq_pair_id_timespan_timestamp(pair_id, timespan, timestamp);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candle DROP KEY uniq_pair_id_timespan_timestamp;');
    }


    public function isTransactional(): bool
    {
        return false;
    }
}

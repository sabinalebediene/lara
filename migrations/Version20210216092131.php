<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216092131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE outfit ADD CONSTRAINT FK_3202960113B3DB11 FOREIGN KEY (master_id) REFERENCES master (id)');
        $this->addSql('CREATE INDEX IDX_3202960113B3DB11 ON outfit (master_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE outfit DROP FOREIGN KEY FK_3202960113B3DB11');
        $this->addSql('DROP INDEX IDX_3202960113B3DB11 ON outfit');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200629080752 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT(5) UNSIGNED ZEROFILL');
        $this->addSql('ALTER TABLE prestation ADD remboursement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FADF61EE8B FOREIGN KEY (remboursement_id) REFERENCES remboursement (id)');
        $this->addSql('CREATE INDEX IDX_51C88FADF61EE8B ON prestation (remboursement_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADF61EE8B');
        $this->addSql('DROP INDEX IDX_51C88FADF61EE8B ON prestation');
        $this->addSql('ALTER TABLE prestation DROP remboursement_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201019123338 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tier ADD compte_id INT DEFAULT NULL, ADD contact VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(1) NOT NULL');
        $this->addSql('ALTER TABLE tier ADD CONSTRAINT FK_249E978AF2C56620 FOREIGN KEY (compte_id) REFERENCES compte (id)');
        $this->addSql('CREATE INDEX IDX_249E978AF2C56620 ON tier (compte_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B89D40298');
        $this->addSql('DROP INDEX IDX_73F2F77B89D40298 ON budget');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADA76ED395');
        $this->addSql('DROP INDEX IDX_51C88FADA76ED395 ON prestation');
        $this->addSql('ALTER TABLE tier DROP FOREIGN KEY FK_249E978AF2C56620');
        $this->addSql('DROP INDEX IDX_249E978AF2C56620 ON tier');
        $this->addSql('ALTER TABLE tier DROP compte_id, DROP contact, DROP adresse, DROP type');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200623075806 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D630680489D40298');
        $this->addSql('DROP INDEX IDX_D630680489D40298 ON historique_cotisation');
        $this->addSql('ALTER TABLE historique_cotisation ADD reference VARCHAR(255) NOT NULL, DROP exercice_id, CHANGE annee moyen VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation ADD exercice_id INT NOT NULL, ADD annee VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP moyen, DROP reference');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D630680489D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_D630680489D40298 ON historique_cotisation (exercice_id)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200624095924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D630680425F06C53');
        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D630680489D40298');
        $this->addSql('DROP INDEX IDX_D630680425F06C53 ON historique_cotisation');
        $this->addSql('DROP INDEX IDX_D630680489D40298 ON historique_cotisation');
        $this->addSql('ALTER TABLE historique_cotisation ADD compte_cotisation_id INT NOT NULL, DROP adherent_id, DROP exercice_id, DROP nb_ancien, DROP nb_nouveau');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D6306804ED105ACE FOREIGN KEY (compte_cotisation_id) REFERENCES compte_cotisation (id)');
        $this->addSql('CREATE INDEX IDX_D6306804ED105ACE ON historique_cotisation (compte_cotisation_id)');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT(5) UNSIGNED ZEROFILL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D6306804ED105ACE');
        $this->addSql('DROP INDEX IDX_D6306804ED105ACE ON historique_cotisation');
        $this->addSql('ALTER TABLE historique_cotisation ADD exercice_id INT NOT NULL, ADD nb_ancien INT NOT NULL, ADD nb_nouveau INT NOT NULL, CHANGE compte_cotisation_id adherent_id INT NOT NULL');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D630680425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D630680489D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_D630680425F06C53 ON historique_cotisation (adherent_id)');
        $this->addSql('CREATE INDEX IDX_D630680489D40298 ON historique_cotisation (exercice_id)');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT UNSIGNED DEFAULT NULL');
    }
}

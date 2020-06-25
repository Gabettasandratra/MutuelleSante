<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200624062358 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE compte_cotisation (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, is_paye TINYINT(1) NOT NULL, reste DOUBLE PRECISION NOT NULL, nouveau INT NOT NULL, ancien INT NOT NULL, r_ancien INT NOT NULL, r_nouveau INT NOT NULL, INDEX IDX_66700D5325F06C53 (adherent_id), INDEX IDX_66700D5389D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compte_cotisation ADD CONSTRAINT FK_66700D5325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE compte_cotisation ADD CONSTRAINT FK_66700D5389D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6306804AEA34913 ON historique_cotisation (reference)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE compte_cotisation');
        $this->addSql('DROP INDEX UNIQ_D6306804AEA34913 ON historique_cotisation');
    }
}

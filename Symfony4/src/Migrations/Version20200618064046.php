<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618064046 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE taille_famille');
        $this->addSql('ALTER TABLE adherent ADD taille_famille LONGTEXT COMMENT \'(DC2Type:array)\', CHANGE garantie_id garantie_id INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE taille_famille (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, mois1 INT NOT NULL, mois2 INT NOT NULL, mois3 INT NOT NULL, mois4 INT NOT NULL, mois5 INT NOT NULL, mois6 INT NOT NULL, mois7 INT NOT NULL, mois8 INT NOT NULL, mois9 INT NOT NULL, mois10 INT NOT NULL, mois11 INT NOT NULL, mois12 INT NOT NULL, UNIQUE INDEX UNIQ_F61EF83625F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE taille_famille ADD CONSTRAINT FK_F61EF83625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE adherent DROP taille_famille, CHANGE garantie_id garantie_id INT DEFAULT 1 NOT NULL');
    }
}

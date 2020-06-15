<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200615103445 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE etat_adherent (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, date_saisie DATETIME NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_D0658F025F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etat_pac (id INT AUTO_INCREMENT NOT NULL, pac_id INT NOT NULL, date_saisie DATETIME NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_453602DEAE21B650 (pac_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pac (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, code_mutuelle VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, sexe VARCHAR(255) NOT NULL, date_naissance DATETIME NOT NULL, parente VARCHAR(255) NOT NULL, date_entrer DATETIME NOT NULL, created_at DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, INDEX IDX_3EDDB4625F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, is_actif TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etat_adherent ADD CONSTRAINT FK_D0658F025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE etat_pac ADD CONSTRAINT FK_453602DEAE21B650 FOREIGN KEY (pac_id) REFERENCES pac (id)');
        $this->addSql('ALTER TABLE pac ADD CONSTRAINT FK_3EDDB4625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE adherent CHANGE date_saisie created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE etat_pac DROP FOREIGN KEY FK_453602DEAE21B650');
        $this->addSql('DROP TABLE etat_adherent');
        $this->addSql('DROP TABLE etat_pac');
        $this->addSql('DROP TABLE pac');
        $this->addSql('DROP TABLE zone');
        $this->addSql('ALTER TABLE adherent CHANGE created_at date_saisie DATETIME NOT NULL');
    }
}

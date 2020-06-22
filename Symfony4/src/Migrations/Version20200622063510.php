<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200622063510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adherent DROP FOREIGN KEY FK_90D3F060A4B9602F');
        $this->addSql('ALTER TABLE garantie DROP FOREIGN KEY FK_7193C6284E473384');
        $this->addSql('DROP TABLE etat_adherent');
        $this->addSql('DROP TABLE etat_pac');
        $this->addSql('DROP TABLE garantie');
        $this->addSql('DROP TABLE type_cotisation');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP INDEX IDX_90D3F060A4B9602F ON adherent');
        $this->addSql('ALTER TABLE adherent DROP garantie_id, DROP taille_famille');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE etat_adherent (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, date_saisie DATETIME NOT NULL, etat VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_D0658F025F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE etat_pac (id INT AUTO_INCREMENT NOT NULL, pac_id INT NOT NULL, date_saisie DATETIME NOT NULL, etat VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_453602DEAE21B650 (pac_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE garantie (id INT AUTO_INCREMENT NOT NULL, type_cotisation_id INT NOT NULL, nom VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, droit_adhesion DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, delai_retard INT DEFAULT NULL, delai_reprise INT DEFAULT NULL, periode_observation INT NOT NULL, montant1 DOUBLE PRECISION NOT NULL, INDEX IDX_7193C6284E473384 (type_cotisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE type_cotisation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, is_actif TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE etat_adherent ADD CONSTRAINT FK_D0658F025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE etat_pac ADD CONSTRAINT FK_453602DEAE21B650 FOREIGN KEY (pac_id) REFERENCES pac (id)');
        $this->addSql('ALTER TABLE garantie ADD CONSTRAINT FK_7193C6284E473384 FOREIGN KEY (type_cotisation_id) REFERENCES type_cotisation (id)');
        $this->addSql('ALTER TABLE adherent ADD garantie_id INT NOT NULL, ADD taille_famille LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE adherent ADD CONSTRAINT FK_90D3F060A4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id)');
        $this->addSql('CREATE INDEX IDX_90D3F060A4B9602F ON adherent (garantie_id)');
    }
}

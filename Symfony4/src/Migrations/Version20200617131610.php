<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200617131610 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE garantie (id INT AUTO_INCREMENT NOT NULL, type_cotisation_id INT NOT NULL, nom VARCHAR(255) NOT NULL, droit_adhesion DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, delai_retard INT DEFAULT 0, delai_reprise INT DEFAULT 0, periode_observation INT NOT NULL, montant1 DOUBLE PRECISION NOT NULL, INDEX IDX_7193C6284E473384 (type_cotisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_cotisation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE garantie ADD CONSTRAINT FK_7193C6284E473384 FOREIGN KEY (type_cotisation_id) REFERENCES type_cotisation (id)');
        $this->addSql('ALTER TABLE pac CHANGE is_sortie is_sortie TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE garantie DROP FOREIGN KEY FK_7193C6284E473384');
        $this->addSql('DROP TABLE garantie');
        $this->addSql('DROP TABLE type_cotisation');
        $this->addSql('ALTER TABLE pac CHANGE is_sortie is_sortie TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}

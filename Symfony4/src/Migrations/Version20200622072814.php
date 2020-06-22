<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200622072814 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE acte_soin (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, compte_debit_id INT NOT NULL, compte_credit_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, libelle VARCHAR(255) NOT NULL, piece VARCHAR(255) NOT NULL, analytique VARCHAR(255) NOT NULL, date DATETIME NOT NULL, categorie VARCHAR(255) NOT NULL, moyen VARCHAR(255) NOT NULL, INDEX IDX_23A0E66C6FE1113 (compte_debit_id), INDEX IDX_23A0E66D8811CB (compte_credit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compte (id INT AUTO_INCREMENT NOT NULL, poste VARCHAR(10) NOT NULL, titre VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, type TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, photo VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66C6FE1113 FOREIGN KEY (compte_debit_id) REFERENCES compte (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66D8811CB FOREIGN KEY (compte_credit_id) REFERENCES compte (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90D3F060F55AE19E ON adherent (numero)');
        $this->addSql('ALTER TABLE exercice ADD cot_nouveau DOUBLE PRECISION NOT NULL, ADD cot_ancien DOUBLE PRECISION NOT NULL, ADD droit_adhesion DOUBLE PRECISION NOT NULL, ADD date_debut DATETIME NOT NULL, ADD date_fin DATETIME NOT NULL');
        $this->addSql('ALTER TABLE pac ADD cin VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66C6FE1113');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66D8811CB');
        $this->addSql('DROP TABLE acte_soin');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE compte');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX UNIQ_90D3F060F55AE19E ON adherent');
        $this->addSql('ALTER TABLE exercice DROP cot_nouveau, DROP cot_ancien, DROP droit_adhesion, DROP date_debut, DROP date_fin');
        $this->addSql('ALTER TABLE pac DROP cin');
    }
}

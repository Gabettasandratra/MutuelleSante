<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200908070306 extends AbstractMigration
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
        $this->addSql('CREATE TABLE adherent (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone1 VARCHAR(255) NOT NULL, telephone2 VARCHAR(255) DEFAULT NULL, date_inscription DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, email VARCHAR(255) DEFAULT NULL, numero INT NOT NULL, code_analytique VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_90D3F060F55AE19E (numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, compte_debit_id INT NOT NULL, compte_credit_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, libelle VARCHAR(255) NOT NULL, piece VARCHAR(255) NOT NULL, analytique VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, categorie VARCHAR(255) NOT NULL, is_ferme TINYINT(1) NOT NULL, INDEX IDX_23A0E66C6FE1113 (compte_debit_id), INDEX IDX_23A0E66D8811CB (compte_credit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compte (id INT AUTO_INCREMENT NOT NULL, poste VARCHAR(10) NOT NULL, titre VARCHAR(255) NOT NULL, type TINYINT(1) NOT NULL, is_tresor TINYINT(1) NOT NULL, note LONGTEXT DEFAULT NULL, classe VARCHAR(255) NOT NULL, code_journal VARCHAR(20) DEFAULT NULL, accept_out TINYINT(1) NOT NULL, accept_in TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CFF652607C890FAB (poste), UNIQUE INDEX UNIQ_CFF65260F900A3D1 (code_journal), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compte_cotisation (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, is_paye TINYINT(1) NOT NULL, reste DOUBLE PRECISION NOT NULL, nouveau INT NOT NULL, ancien INT NOT NULL, paye DOUBLE PRECISION NOT NULL, due DOUBLE PRECISION NOT NULL, INDEX IDX_66700D5325F06C53 (adherent_id), INDEX IDX_66700D5389D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercice (id INT AUTO_INCREMENT NOT NULL, annee VARCHAR(255) NOT NULL, is_cloture TINYINT(1) NOT NULL, cot_nouveau DOUBLE PRECISION NOT NULL, cot_ancien DOUBLE PRECISION NOT NULL, droit_adhesion DOUBLE PRECISION NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historique_cotisation (id INT AUTO_INCREMENT NOT NULL, compte_cotisation_id INT NOT NULL, tresorerie_id INT NOT NULL, article_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, date_paiement DATETIME NOT NULL, created_at DATETIME NOT NULL, reference VARCHAR(255) NOT NULL, remarque LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_D6306804AEA34913 (reference), INDEX IDX_D6306804ED105ACE (compte_cotisation_id), INDEX IDX_D630680472A45225 (tresorerie_id), UNIQUE INDEX UNIQ_D63068047294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pac (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, code_mutuelle INT(5) UNSIGNED ZEROFILL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, sexe VARCHAR(255) NOT NULL, date_naissance DATETIME NOT NULL, parente VARCHAR(255) NOT NULL, date_entrer DATETIME NOT NULL, created_at DATETIME NOT NULL, photo VARCHAR(255) DEFAULT NULL, is_sortie TINYINT(1) NOT NULL, date_sortie DATETIME DEFAULT NULL, remarque VARCHAR(255) DEFAULT NULL, cin VARCHAR(15) DEFAULT NULL, tel VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_3EDDB46ABE530DA (cin), INDEX IDX_3EDDB4625F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parametre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, list LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_ACC790416C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, pac_id INT NOT NULL, adherent_id INT NOT NULL, remboursement_id INT DEFAULT NULL, date DATETIME NOT NULL, designation VARCHAR(255) NOT NULL, frais DOUBLE PRECISION NOT NULL, rembourse DOUBLE PRECISION NOT NULL, prestataire VARCHAR(255) DEFAULT NULL, facture VARCHAR(255) DEFAULT NULL, is_paye TINYINT(1) NOT NULL, decompte INT NOT NULL, status INT NOT NULL, date_decision DATETIME DEFAULT NULL, INDEX IDX_51C88FADAE21B650 (pac_id), INDEX IDX_51C88FAD25F06C53 (adherent_id), INDEX IDX_51C88FADF61EE8B (remboursement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE remboursement (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, tresorerie_id INT NOT NULL, article_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, reference VARCHAR(255) NOT NULL, remarque VARCHAR(255) DEFAULT NULL, INDEX IDX_C0C0D9EF25F06C53 (adherent_id), INDEX IDX_C0C0D9EF89D40298 (exercice_id), INDEX IDX_C0C0D9EF72A45225 (tresorerie_id), UNIQUE INDEX UNIQ_C0C0D9EF7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, photo VARCHAR(255) NOT NULL, fonction VARCHAR(255) NOT NULL, date DATETIME NOT NULL, lost TINYINT(1) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66C6FE1113 FOREIGN KEY (compte_debit_id) REFERENCES compte (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66D8811CB FOREIGN KEY (compte_credit_id) REFERENCES compte (id)');
        $this->addSql('ALTER TABLE compte_cotisation ADD CONSTRAINT FK_66700D5325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE compte_cotisation ADD CONSTRAINT FK_66700D5389D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D6306804ED105ACE FOREIGN KEY (compte_cotisation_id) REFERENCES compte_cotisation (id)');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D630680472A45225 FOREIGN KEY (tresorerie_id) REFERENCES compte (id)');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D63068047294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE pac ADD CONSTRAINT FK_3EDDB4625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FADAE21B650 FOREIGN KEY (pac_id) REFERENCES pac (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FADF61EE8B FOREIGN KEY (remboursement_id) REFERENCES remboursement (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF89D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF72A45225 FOREIGN KEY (tresorerie_id) REFERENCES compte (id)');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE compte_cotisation DROP FOREIGN KEY FK_66700D5325F06C53');
        $this->addSql('ALTER TABLE pac DROP FOREIGN KEY FK_3EDDB4625F06C53');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD25F06C53');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF25F06C53');
        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D63068047294869C');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF7294869C');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66C6FE1113');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66D8811CB');
        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D630680472A45225');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF72A45225');
        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D6306804ED105ACE');
        $this->addSql('ALTER TABLE compte_cotisation DROP FOREIGN KEY FK_66700D5389D40298');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF89D40298');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADAE21B650');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADF61EE8B');
        $this->addSql('DROP TABLE acte_soin');
        $this->addSql('DROP TABLE adherent');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE compte');
        $this->addSql('DROP TABLE compte_cotisation');
        $this->addSql('DROP TABLE exercice');
        $this->addSql('DROP TABLE historique_cotisation');
        $this->addSql('DROP TABLE pac');
        $this->addSql('DROP TABLE parametre');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE remboursement');
        $this->addSql('DROP TABLE user');
    }
}

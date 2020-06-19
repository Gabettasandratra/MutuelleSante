<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200619172557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE arriere_avance');
        $this->addSql('DROP TABLE cotisation_emise');
        $this->addSql('DROP TABLE cotisation_percue');
        $this->addSql('ALTER TABLE historique_cotisation ADD annee VARCHAR(255) NOT NULL, DROP month');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE arriere_avance (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, cotisations LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', INDEX IDX_5AC5680825F06C53 (adherent_id), INDEX IDX_5AC5680889D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cotisation_emise (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, cotisations LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', INDEX IDX_9405B77425F06C53 (adherent_id), INDEX IDX_9405B77489D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cotisation_percue (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, cotisations LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', INDEX IDX_AB1739E25F06C53 (adherent_id), INDEX IDX_AB1739E89D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE arriere_avance ADD CONSTRAINT FK_5AC5680825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE arriere_avance ADD CONSTRAINT FK_5AC5680889D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE cotisation_emise ADD CONSTRAINT FK_9405B77425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE cotisation_emise ADD CONSTRAINT FK_9405B77489D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE cotisation_percue ADD CONSTRAINT FK_AB1739E25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE cotisation_percue ADD CONSTRAINT FK_AB1739E89D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE historique_cotisation ADD month INT NOT NULL, DROP annee');
    }
}

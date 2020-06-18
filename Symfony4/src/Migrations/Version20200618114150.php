<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618114150 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cotisation_emise (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, cotisations LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_9405B77425F06C53 (adherent_id), INDEX IDX_9405B77489D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cotisation_percue (id INT AUTO_INCREMENT NOT NULL, adherent_id INT NOT NULL, exercice_id INT NOT NULL, cotisations LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_AB1739E25F06C53 (adherent_id), INDEX IDX_AB1739E89D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercice (id INT AUTO_INCREMENT NOT NULL, annee VARCHAR(255) NOT NULL, is_cloture TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cotisation_emise ADD CONSTRAINT FK_9405B77425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE cotisation_emise ADD CONSTRAINT FK_9405B77489D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE cotisation_percue ADD CONSTRAINT FK_AB1739E25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE cotisation_percue ADD CONSTRAINT FK_AB1739E89D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE adherent CHANGE taille_famille taille_famille LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cotisation_emise DROP FOREIGN KEY FK_9405B77489D40298');
        $this->addSql('ALTER TABLE cotisation_percue DROP FOREIGN KEY FK_AB1739E89D40298');
        $this->addSql('DROP TABLE cotisation_emise');
        $this->addSql('DROP TABLE cotisation_percue');
        $this->addSql('DROP TABLE exercice');
        $this->addSql('ALTER TABLE adherent CHANGE taille_famille taille_famille LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\'');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200707064544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE article ADD is_ferme TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT(5) UNSIGNED ZEROFILL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ACC790416C6E55B5 ON parametre (nom)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE article DROP is_ferme');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT UNSIGNED DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_ACC790416C6E55B5 ON parametre');
    }
}

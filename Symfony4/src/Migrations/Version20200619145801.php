<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200619145801 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_90D3F060713949C5 ON adherent');
        $this->addSql('ALTER TABLE adherent DROP code_mutuelle, DROP prenom, DROP sexe, DROP date_naissance, DROP profession, DROP salaire, DROP email');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adherent ADD code_mutuelle VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci, ADD prenom VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD sexe VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD date_naissance DATETIME NOT NULL, ADD profession VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD salaire DOUBLE PRECISION DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90D3F060713949C5 ON adherent (code_mutuelle)');
    }
}

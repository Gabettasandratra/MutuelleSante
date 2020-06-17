<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200617192424 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adherent ADD garantie_id INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE adherent ADD CONSTRAINT FK_90D3F060A4B9602F FOREIGN KEY (garantie_id) REFERENCES garantie (id)');
        $this->addSql('CREATE INDEX IDX_90D3F060A4B9602F ON adherent (garantie_id)');
        $this->addSql('ALTER TABLE garantie CHANGE delai_retard delai_retard INT DEFAULT NULL, CHANGE delai_reprise delai_reprise INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adherent DROP FOREIGN KEY FK_90D3F060A4B9602F');
        $this->addSql('DROP INDEX IDX_90D3F060A4B9602F ON adherent');
        $this->addSql('ALTER TABLE adherent DROP garantie_id');
        $this->addSql('ALTER TABLE garantie CHANGE delai_retard delai_retard INT DEFAULT 0, CHANGE delai_reprise delai_reprise INT DEFAULT 0');
    }
}

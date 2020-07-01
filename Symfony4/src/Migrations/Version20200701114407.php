<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701114407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation ADD tresorerie_id INT NOT NULL, DROP moyen');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D630680472A45225 FOREIGN KEY (tresorerie_id) REFERENCES compte (id)');
        $this->addSql('CREATE INDEX IDX_D630680472A45225 ON historique_cotisation (tresorerie_id)');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT(5) UNSIGNED ZEROFILL');
        $this->addSql('ALTER TABLE remboursement ADD tresorerie_id INT NOT NULL, DROP moyen');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF72A45225 FOREIGN KEY (tresorerie_id) REFERENCES compte (id)');
        $this->addSql('CREATE INDEX IDX_C0C0D9EF72A45225 ON remboursement (tresorerie_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D630680472A45225');
        $this->addSql('DROP INDEX IDX_D630680472A45225 ON historique_cotisation');
        $this->addSql('ALTER TABLE historique_cotisation ADD moyen VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP tresorerie_id');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF72A45225');
        $this->addSql('DROP INDEX IDX_C0C0D9EF72A45225 ON remboursement');
        $this->addSql('ALTER TABLE remboursement ADD moyen VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP tresorerie_id');
    }
}

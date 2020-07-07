<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200707082110 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation ADD article_id INT NOT NULL');
        $this->addSql('ALTER TABLE historique_cotisation ADD CONSTRAINT FK_D63068047294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D63068047294869C ON historique_cotisation (article_id)');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT(5) UNSIGNED ZEROFILL');
        $this->addSql('ALTER TABLE remboursement ADD article_id INT NOT NULL');
        $this->addSql('ALTER TABLE remboursement ADD CONSTRAINT FK_C0C0D9EF7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0C0D9EF7294869C ON remboursement (article_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE historique_cotisation DROP FOREIGN KEY FK_D63068047294869C');
        $this->addSql('DROP INDEX UNIQ_D63068047294869C ON historique_cotisation');
        $this->addSql('ALTER TABLE historique_cotisation DROP article_id');
        $this->addSql('ALTER TABLE pac CHANGE code_mutuelle code_mutuelle INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE remboursement DROP FOREIGN KEY FK_C0C0D9EF7294869C');
        $this->addSql('DROP INDEX UNIQ_C0C0D9EF7294869C ON remboursement');
        $this->addSql('ALTER TABLE remboursement DROP article_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201020101409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        
        $this->addSql('CREATE UNIQUE INDEX UNIQ_249E978A77153098 ON tier (code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_45AB633677153098 ON analytique');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66345D6718');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66A354F9DC');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6636ABA6B8');
        $this->addSql('DROP INDEX IDX_23A0E66345D6718 ON article');
        $this->addSql('DROP INDEX IDX_23A0E66A354F9DC ON article');
        $this->addSql('DROP INDEX IDX_23A0E6636ABA6B8 ON article');
        $this->addSql('ALTER TABLE article DROP analytic_id, DROP tier_id, DROP budget_id');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B89D40298');
        $this->addSql('DROP INDEX UNIQ_73F2F77B77153098 ON budget');
        $this->addSql('DROP INDEX IDX_73F2F77B89D40298 ON budget');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADA76ED395');
        $this->addSql('DROP INDEX IDX_51C88FADA76ED395 ON prestation');
        $this->addSql('DROP INDEX UNIQ_249E978A77153098 ON tier');
    }
}

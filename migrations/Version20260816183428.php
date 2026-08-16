<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260816183428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE department ADD region_erm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A2971434C FOREIGN KEY (region_erm_id) REFERENCES region_erm (id)');
        $this->addSql('CREATE INDEX IDX_CD1DE18A2971434C ON department (region_erm_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A2971434C');
        $this->addSql('DROP INDEX IDX_CD1DE18A2971434C ON department');
        $this->addSql('ALTER TABLE department DROP region_erm_id');
    }
}

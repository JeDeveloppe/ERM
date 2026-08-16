<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260816171612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD region_erm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1762971434C FOREIGN KEY (region_erm_id) REFERENCES region_erm (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD1762971434C ON person (region_erm_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1762971434C');
        $this->addSql('DROP INDEX UNIQ_34DCD1762971434C ON person');
        $this->addSql('ALTER TABLE person DROP region_erm_id');
    }
}

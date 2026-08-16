<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260816111125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technician_work_for_shop (technician_id INT NOT NULL, shop_id INT NOT NULL, INDEX IDX_BC1CA901E6C5D496 (technician_id), INDEX IDX_BC1CA9014D16C4DD (shop_id), PRIMARY KEY(technician_id, shop_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_work_for_shop ADD CONSTRAINT FK_BC1CA901E6C5D496 FOREIGN KEY (technician_id) REFERENCES technician (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_work_for_shop ADD CONSTRAINT FK_BC1CA9014D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technical_advisor DROP FOREIGN KEY FK_75E16C452FEAA154');
        $this->addSql('ALTER TABLE technical_advisor DROP FOREIGN KEY FK_75E16C45783E3463');
        $this->addSql('ALTER TABLE technical_advisor_shop DROP FOREIGN KEY FK_EAC4941F4D16C4DD');
        $this->addSql('ALTER TABLE technical_advisor_shop DROP FOREIGN KEY FK_EAC4941FE557C2B');
        $this->addSql('DROP TABLE technical_advisor');
        $this->addSql('DROP TABLE technical_advisor_shop');
        $this->addSql('ALTER TABLE technician ADD manager_id INT DEFAULT NULL, ADD is_technician_advisor TINYINT(1) NOT NULL, ADD zone_color VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E948783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id)');
        $this->addSql('CREATE INDEX IDX_F244E948783E3463 ON technician (manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE technical_advisor (id INT AUTO_INCREMENT NOT NULL, manager_id INT NOT NULL, attachment_center_id INT NOT NULL, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, zone_color VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_75E16C452FEAA154 (attachment_center_id), INDEX IDX_75E16C45783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE technical_advisor_shop (technical_advisor_id INT NOT NULL, shop_id INT NOT NULL, INDEX IDX_EAC4941F4D16C4DD (shop_id), INDEX IDX_EAC4941FE557C2B (technical_advisor_id), PRIMARY KEY(technical_advisor_id, shop_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE technical_advisor ADD CONSTRAINT FK_75E16C452FEAA154 FOREIGN KEY (attachment_center_id) REFERENCES shop (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technical_advisor ADD CONSTRAINT FK_75E16C45783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technical_advisor_shop ADD CONSTRAINT FK_EAC4941F4D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technical_advisor_shop ADD CONSTRAINT FK_EAC4941FE557C2B FOREIGN KEY (technical_advisor_id) REFERENCES technical_advisor (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_work_for_shop DROP FOREIGN KEY FK_BC1CA901E6C5D496');
        $this->addSql('ALTER TABLE technician_work_for_shop DROP FOREIGN KEY FK_BC1CA9014D16C4DD');
        $this->addSql('DROP TABLE technician_work_for_shop');
        $this->addSql('ALTER TABLE technician DROP FOREIGN KEY FK_F244E948783E3463');
        $this->addSql('DROP INDEX IDX_F244E948783E3463 ON technician');
        $this->addSql('ALTER TABLE technician DROP manager_id, DROP is_technician_advisor, DROP zone_color');
    }
}

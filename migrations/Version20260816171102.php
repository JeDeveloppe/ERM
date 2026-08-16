<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260816171102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration est auto-générée puis retouchée à la main suite à une
        // exécution partielle (voir historique de session) : person/role_erm + tables
        // de jointure créées, manager/manager_class/technician(+jointures) déjà
        // supprimées. Il ne reste que le retargetage final cgo/shop/zone_erm.
        $this->addSql('ALTER TABLE cgo ADD CONSTRAINT FK_42612DC2783E3463 FOREIGN KEY (manager_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE shop DROP manager_id');
        $this->addSql('ALTER TABLE zone_erm ADD CONSTRAINT FK_B518E884783E3463 FOREIGN KEY (manager_id) REFERENCES person (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cgo DROP FOREIGN KEY FK_42612DC2783E3463');
        $this->addSql('ALTER TABLE zone_erm DROP FOREIGN KEY FK_B518E884783E3463');
        $this->addSql('CREATE TABLE manager (id INT AUTO_INCREMENT NOT NULL, region_erm_id INT DEFAULT NULL, zone_erm_id INT DEFAULT NULL, manager_class_id INT DEFAULT NULL, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_FA2425B91E6A7374 (manager_class_id), UNIQUE INDEX UNIQ_FA2425B92971434C (region_erm_id), UNIQUE INDEX UNIQ_FA2425B95B6976B (zone_erm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE manager_class (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE technician (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, vehicle_id INT NOT NULL, controled_by_cgo_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, manager_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, informations LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, is_telematic TINYINT(1) NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_technician_advisor TINYINT(1) NOT NULL, zone_color VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, is_rcs TINYINT(1) NOT NULL, INDEX IDX_F244E9484D16C4DD (shop_id), INDEX IDX_F244E948545317D1 (vehicle_id), INDEX IDX_F244E948783E3463 (manager_id), INDEX IDX_F244E948896DBBDE (updated_by_id), INDEX IDX_F244E94889E96EFD (controled_by_cgo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE technician_technician_fonction (technician_id INT NOT NULL, technician_fonction_id INT NOT NULL, INDEX IDX_76AB542F4C733B34 (technician_fonction_id), INDEX IDX_76AB542FE6C5D496 (technician_id), PRIMARY KEY(technician_id, technician_fonction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE technician_technician_formations (technician_id INT NOT NULL, technician_formations_id INT NOT NULL, INDEX IDX_995805B22D83BEA8 (technician_formations_id), INDEX IDX_995805B2E6C5D496 (technician_id), PRIMARY KEY(technician_id, technician_formations_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE technician_work_for_shop (technician_id INT NOT NULL, shop_id INT NOT NULL, INDEX IDX_BC1CA9014D16C4DD (shop_id), INDEX IDX_BC1CA901E6C5D496 (technician_id), PRIMARY KEY(technician_id, shop_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE manager ADD CONSTRAINT FK_FA2425B91E6A7374 FOREIGN KEY (manager_class_id) REFERENCES manager_class (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE manager ADD CONSTRAINT FK_FA2425B92971434C FOREIGN KEY (region_erm_id) REFERENCES region_erm (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE manager ADD CONSTRAINT FK_FA2425B95B6976B FOREIGN KEY (zone_erm_id) REFERENCES zone_erm (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E9484D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E948545317D1 FOREIGN KEY (vehicle_id) REFERENCES technician_vehicle (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E948783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E948896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E94889E96EFD FOREIGN KEY (controled_by_cgo_id) REFERENCES cgo (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE technician_technician_fonction ADD CONSTRAINT FK_76AB542F4C733B34 FOREIGN KEY (technician_fonction_id) REFERENCES technician_fonction (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_technician_fonction ADD CONSTRAINT FK_76AB542FE6C5D496 FOREIGN KEY (technician_id) REFERENCES technician (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_technician_formations ADD CONSTRAINT FK_995805B22D83BEA8 FOREIGN KEY (technician_formations_id) REFERENCES technician_formations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_technician_formations ADD CONSTRAINT FK_995805B2E6C5D496 FOREIGN KEY (technician_id) REFERENCES technician (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_work_for_shop ADD CONSTRAINT FK_BC1CA9014D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_work_for_shop ADD CONSTRAINT FK_BC1CA901E6C5D496 FOREIGN KEY (technician_id) REFERENCES technician (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1764D16C4DD');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176545317D1');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17689E96EFD');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176783E3463');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176896DBBDE');
        $this->addSql('ALTER TABLE person_technician_formations DROP FOREIGN KEY FK_D6759D22217BBB47');
        $this->addSql('ALTER TABLE person_technician_formations DROP FOREIGN KEY FK_D6759D222D83BEA8');
        $this->addSql('ALTER TABLE person_work_for_shop DROP FOREIGN KEY FK_A2E494A6217BBB47');
        $this->addSql('ALTER TABLE person_work_for_shop DROP FOREIGN KEY FK_A2E494A64D16C4DD');
        $this->addSql('ALTER TABLE person_technician_fonction DROP FOREIGN KEY FK_7E69B97C217BBB47');
        $this->addSql('ALTER TABLE person_technician_fonction DROP FOREIGN KEY FK_7E69B97C4C733B34');
        $this->addSql('ALTER TABLE person_role_erm DROP FOREIGN KEY FK_BF73F70D217BBB47');
        $this->addSql('ALTER TABLE person_role_erm DROP FOREIGN KEY FK_BF73F70D91835EA8');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_technician_formations');
        $this->addSql('DROP TABLE person_work_for_shop');
        $this->addSql('DROP TABLE person_technician_fonction');
        $this->addSql('DROP TABLE person_role_erm');
        $this->addSql('DROP TABLE role_erm');
        $this->addSql('ALTER TABLE cgo DROP FOREIGN KEY FK_42612DC2783E3463');
        $this->addSql('ALTER TABLE cgo ADD CONSTRAINT FK_42612DC2783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE shop ADD manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA2783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AC6A4CA2783E3463 ON shop (manager_id)');
        $this->addSql('ALTER TABLE zone_erm DROP FOREIGN KEY FK_B518E884783E3463');
        $this->addSql('ALTER TABLE zone_erm ADD CONSTRAINT FK_B518E884783E3463 FOREIGN KEY (manager_id) REFERENCES manager (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}

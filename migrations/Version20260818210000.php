<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Conversion Manager+ManagerClass+Technician+TechnicalAdvisor vers Person+RoleErm (prod)
 */
final class Version20260818210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Conversion Manager+ManagerClass+Technician+TechnicalAdvisor vers Person+RoleErm (prod)';
    }

    public function up(Schema $schema): void
    {
        // Migration à sens unique pour convertir l'ancien schéma de production
        // (Manager/ManagerClass/Technician/TechnicalAdvisor) vers Person/RoleErm.
        // No-op sur tout environnement qui n'a pas ces tables (dev, tests, nouveaux
        // clones) : elles n'y ont jamais existé sous cette forme.
        if (!$schema->hasTable('manager')) {
            return;
        }

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('CREATE TABLE `role_erm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('CREATE TABLE `person` (
  `id` int NOT NULL,
  `shop_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `controled_by_cgo_id` int DEFAULT NULL,
  `manager_id` int DEFAULT NULL,
  `updated_by_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `informations` longtext,
  `zone_color` varchar(10) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `region_erm_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('CREATE TABLE `person_role_erm` (
  `person_id` int NOT NULL,
  `role_erm_id` int NOT NULL,
  PRIMARY KEY (`person_id`,`role_erm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('CREATE TABLE `person_technician_fonction` (
  `person_id` int NOT NULL,
  `technician_fonction_id` int NOT NULL,
  PRIMARY KEY (`person_id`,`technician_fonction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('CREATE TABLE `person_technician_formations` (
  `person_id` int NOT NULL,
  `technician_formations_id` int NOT NULL,
  PRIMARY KEY (`person_id`,`technician_formations_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('CREATE TABLE `person_work_for_shop` (
  `person_id` int NOT NULL,
  `shop_id` int NOT NULL,
  PRIMARY KEY (`person_id`,`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('INSERT INTO role_erm (id, name, color) VALUES
  (1, \'DO\', NULL),
  (2, \'DOA VI\', NULL),
  (3, \'DOA VL\', NULL),
  (4, \'DR\', NULL),
  (5, \'AO\', NULL),
  (6, \'RZ\', NULL),
  (7, \'RAVL\', NULL),
  (8, \'RCS\', \'#2ecc71\'),
  (9, \'RCS MULTI SITE\', NULL),
  (10, \'TM\', NULL),
  (11, \'TECHNICIEN TELEMATIQUE\', NULL),
  (12, \'CT\', NULL),
  (13, \'RCGO VI\', NULL),
  (14, \'RCGO VL\', NULL)');
        $this->addSql('INSERT INTO person (id, name, first_name, phone, email, informations, zone_color, updated_at)
SELECT m.id, m.last_name, m.first_name, m.phone, m.email, NULL, NULL, NULL
FROM manager m');
        $this->addSql('INSERT INTO person_role_erm (person_id, role_erm_id)
SELECT m.id, CASE mc.name
    WHEN \'AO\' THEN 5
    WHEN \'DR\' THEN 4
    WHEN \'RAVL\' THEN 7
    WHEN \'RZ\' THEN 6
  END
FROM manager m
JOIN manager_class mc ON mc.id = m.manager_class_id
WHERE mc.name IN (\'AO\',\'DR\',\'RAVL\',\'RZ\')');
        $this->addSql('UPDATE person p
JOIN (
    SELECT manager_id, MIN(id) AS primary_shop_id, COUNT(*) AS nb_shops
    FROM shop
    WHERE manager_id IS NOT NULL
    GROUP BY manager_id
) rcs ON rcs.manager_id = p.id
SET p.shop_id = rcs.primary_shop_id');
        $this->addSql('INSERT INTO person_role_erm (person_id, role_erm_id)
SELECT s.manager_id, IF(cnt.nb_shops > 1, 9, 8)
FROM (SELECT DISTINCT manager_id FROM shop WHERE manager_id IS NOT NULL) s
JOIN (SELECT manager_id, COUNT(*) AS nb_shops FROM shop WHERE manager_id IS NOT NULL GROUP BY manager_id) cnt
  ON cnt.manager_id = s.manager_id');
        $this->addSql('INSERT INTO person_work_for_shop (person_id, shop_id)
SELECT s.manager_id, s.id
FROM shop s
JOIN (
    SELECT manager_id, MIN(id) AS primary_shop_id
    FROM shop
    WHERE manager_id IS NOT NULL
    GROUP BY manager_id
) primaire ON primaire.manager_id = s.manager_id
WHERE s.manager_id IS NOT NULL AND s.id <> primaire.primary_shop_id');
        $this->addSql('INSERT INTO person_role_erm (person_id, role_erm_id)
SELECT DISTINCT c.manager_id, CASE sc.name WHEN \'MV\' THEN 13 WHEN \'VL\' THEN 14 END
FROM cgo c
JOIN shop_class sc ON sc.id = c.class_erm_id
WHERE sc.name IN (\'MV\',\'VL\')');
        $this->addSql('INSERT INTO person (id, shop_id, vehicle_id, controled_by_cgo_id, updated_by_id,
                     name, first_name, phone, email, informations, zone_color, updated_at)
SELECT t.id + 10000, t.shop_id, t.vehicle_id, t.controled_by_cgo_id, t.updated_by_id,
       t.name, t.first_name, t.phone, t.email, t.informations, NULL, t.updated_at
FROM technician t');
        $this->addSql('INSERT INTO person_role_erm (person_id, role_erm_id)
SELECT t.id + 10000, 10 FROM technician t');
        $this->addSql('INSERT INTO person_role_erm (person_id, role_erm_id)
SELECT t.id + 10000, 11 FROM technician t WHERE t.is_telematic = 1');
        $this->addSql('INSERT INTO person_technician_fonction (person_id, technician_fonction_id)
SELECT technician_id + 10000, technician_fonction_id FROM technician_technician_fonction');
        $this->addSql('INSERT INTO person_technician_formations (person_id, technician_formations_id)
SELECT technician_id + 10000, technician_formations_id FROM technician_technician_formations');
        $this->addSql('INSERT INTO person (id, shop_id, manager_id, name, first_name, phone, email, zone_color)
SELECT ta.id + 20000, ta.attachment_center_id, ta.manager_id,
       ta.last_name, ta.first_name, ta.phone, ta.email, ta.zone_color
FROM technical_advisor ta');
        $this->addSql('INSERT INTO person_role_erm (person_id, role_erm_id)
SELECT ta.id + 20000, 12 FROM technical_advisor ta');
        $this->addSql('INSERT INTO person_work_for_shop (person_id, shop_id)
SELECT technical_advisor_id + 20000, shop_id FROM technical_advisor_shop');
        $this->addSql('UPDATE cgo SET manager_id = manager_id WHERE manager_id IS NOT NULL');
        $this->addSql('-- manager.id inchangé, no-op explicite
UPDATE zone_erm SET manager_id = manager_id WHERE manager_id IS NOT NULL');
        $this->addSql('-- idem (toujours NULL en prod)
ALTER TABLE cgo DROP FOREIGN KEY FK_42612DC2783E3463');
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA2783E3463');
        $this->addSql('ALTER TABLE technical_advisor DROP FOREIGN KEY FK_75E16C45783E3463');
        $this->addSql('ALTER TABLE technical_advisor_shop DROP FOREIGN KEY FK_EAC4941FE557C2B');
        $this->addSql('ALTER TABLE technician_technician_fonction DROP FOREIGN KEY FK_76AB542FE6C5D496');
        $this->addSql('ALTER TABLE technician_technician_formations DROP FOREIGN KEY FK_995805B2E6C5D496');
        $this->addSql('ALTER TABLE zone_erm DROP FOREIGN KEY FK_B518E884783E3463');
        $this->addSql('ALTER TABLE shop DROP COLUMN manager_id');
        $this->addSql('DROP TABLE technician_technician_fonction');
        $this->addSql('DROP TABLE technician_technician_formations');
        $this->addSql('DROP TABLE technical_advisor_shop');
        $this->addSql('DROP TABLE technical_advisor');
        $this->addSql('DROP TABLE technician');
        $this->addSql('DROP TABLE manager');
        $this->addSql('DROP TABLE manager_class');
        $this->addSql('ALTER TABLE person
  ADD CONSTRAINT FK_person_shop FOREIGN KEY (shop_id) REFERENCES shop (id),
  ADD CONSTRAINT FK_person_vehicle FOREIGN KEY (vehicle_id) REFERENCES technician_vehicle (id),
  ADD CONSTRAINT FK_person_cgo FOREIGN KEY (controled_by_cgo_id) REFERENCES cgo (id),
  ADD CONSTRAINT FK_person_manager FOREIGN KEY (manager_id) REFERENCES person (id),
  ADD CONSTRAINT FK_person_user FOREIGN KEY (updated_by_id) REFERENCES user (id),
  ADD CONSTRAINT FK_person_region FOREIGN KEY (region_erm_id) REFERENCES region_erm (id),
  ADD UNIQUE KEY UNIQ_person_region (region_erm_id)');
        $this->addSql('ALTER TABLE person_role_erm
  ADD CONSTRAINT FK_pre_person FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_pre_role FOREIGN KEY (role_erm_id) REFERENCES role_erm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_technician_fonction
  ADD CONSTRAINT FK_ptf_person FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_ptf_fonction FOREIGN KEY (technician_fonction_id) REFERENCES technician_fonction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_technician_formations
  ADD CONSTRAINT FK_ptfo_person FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_ptfo_formation FOREIGN KEY (technician_formations_id) REFERENCES technician_formations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_work_for_shop
  ADD CONSTRAINT FK_pwfs_person FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_pwfs_shop FOREIGN KEY (shop_id) REFERENCES shop (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cgo
  ADD CONSTRAINT FK_cgo_manager FOREIGN KEY (manager_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE zone_erm
  ADD CONSTRAINT FK_zone_manager FOREIGN KEY (manager_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person MODIFY id int NOT NULL AUTO_INCREMENT');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        throw new \Doctrine\Migrations\Exception\IrreversibleMigration('Migration de conversion de données, non réversible automatiquement.');
    }
}

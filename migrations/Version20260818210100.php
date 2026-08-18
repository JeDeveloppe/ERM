<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Reste des ajustements de schéma (department.region_erm_id, shop.phone, large_region.color, drop telematic_work, index...)
 */
final class Version20260818210100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reste des ajustements de schéma (department.region_erm_id, shop.phone, large_region.color, drop telematic_work, index...)';
    }

    public function up(Schema $schema): void
    {
        // Reste des ajustements de schéma issus du rattrapage de production (voir
        // Version20260818210000). No-op si déjà appliqué (dev a ces changements
        // depuis longtemps via son propre historique de migrations) : la présence
        // de `telematic_work` signale un schéma pas encore rattrapé.
        if (!$schema->hasTable('telematic_work')) {
            return;
        }

        $this->addSql('DROP TABLE telematic_work');
        $this->addSql('ALTER TABLE api_log CHANGE logged_at logged_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE collaborator CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE collaborator_absence CHANGE start_date start_date DATE NOT NULL, CHANGE end_date end_date DATE NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE department ADD region_erm_id INT DEFAULT NULL, DROP simplemap_code');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A2971434C FOREIGN KEY (region_erm_id) REFERENCES region_erm (id)');
        $this->addSql('CREATE INDEX IDX_CD1DE18A2971434C ON department (region_erm_id)');
        $this->addSql('ALTER TABLE large_region ADD color VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE person RENAME INDEX fk_person_shop TO IDX_34DCD1764D16C4DD');
        $this->addSql('ALTER TABLE person RENAME INDEX fk_person_vehicle TO IDX_34DCD176545317D1');
        $this->addSql('ALTER TABLE person RENAME INDEX fk_person_cgo TO IDX_34DCD17689E96EFD');
        $this->addSql('ALTER TABLE person RENAME INDEX fk_person_manager TO IDX_34DCD176783E3463');
        $this->addSql('ALTER TABLE person RENAME INDEX uniq_person_region TO UNIQ_34DCD1762971434C');
        $this->addSql('ALTER TABLE person RENAME INDEX fk_person_user TO IDX_34DCD176896DBBDE');
        $this->addSql('ALTER TABLE person_technician_formations RENAME INDEX fk_ptfo_formation TO IDX_D6759D222D83BEA8');
        $this->addSql('ALTER TABLE person_work_for_shop RENAME INDEX fk_pwfs_shop TO IDX_A2E494A64D16C4DD');
        $this->addSql('ALTER TABLE person_technician_fonction RENAME INDEX fk_ptf_fonction TO IDX_7E69B97C4C733B34');
        $this->addSql('ALTER TABLE person_role_erm RENAME INDEX fk_pre_role TO IDX_BF73F70D91835EA8');
        $this->addSql('ALTER TABLE shop CHANGE phone phone VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE technician_fonction CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE technician_formations CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE technician_vehicle CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE telematic_area CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_visit_at last_visit_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        throw new \Doctrine\Migrations\Exception\IrreversibleMigration('Migration de conversion de données, non réversible automatiquement.');
    }
}
